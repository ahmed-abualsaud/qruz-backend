<?php

namespace App\Repository\Eloquent\Mutations;

use App\Driver;
use App\SchoolTrip;
use App\SchoolTripEntry;
use App\SchoolTripEvent;
use App\SchoolTripRating;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\SchoolTripSchedule;
use App\Helpers\StaticMapUrl;
use App\SchoolTripAttendence;
use App\Jobs\SendPushNotification;
use App\Traits\HandleDeviceTokens;
use App\Exceptions\CustomException;
use App\Events\SchoolTripStatusChanged;
use App\Traits\HandleSchoolTripDeviceTokens;
use App\Traits\HandleSchoolTripStudentStatus;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Mutations\SchoolTripEventRepositoryInterface;

class SchoolTripEventRepository extends BaseRepository implements SchoolTripEventRepositoryInterface
{
    use HandleDeviceTokens;
    use HandleSchoolTripDeviceTokens;
    use HandleSchoolTripStudentStatus;
    
    public function __construct(SchoolTripEvent $model)
    {
        parent::__construct($model);
    }

    public function ready(array $args)
    {
        $trip = $this->getTripById($args['trip_id']);

        if ($trip->log_id) 
            throw new CustomException(__('lang.trip_already_started'));

        $logId = (string) Str::uuid();

        $this->checkAbsence($args['trip_id']);

        $this->checkSchedule($args['trip_id']);

        $this->initTripEvent($args, $logId, $trip->driver_id, $trip->vehicle_id, $trip->supervisor_id);

        $trip->update(['log_id' => $logId, 'ready_at' => date("Y-m-d H:i:s")]);

        return $trip;
    }

    public function start(array $args)
    {
        $trip = $this->getTripById($args['trip_id']);

        if (!$trip->log_id) 
            throw new CustomException(__('lang.driver_not_ready'));

        $payload = [
            'started' => [
                'at' => date("Y-m-d H:i:s"),
                'lat' => $args['latitude'],
                'lng' => $args['longitude']
            ]
        ];

        $event = $this->model->select('content', 'log_id')->findOrFail($trip->log_id);

        $event->update(['content' => array_merge($event->content, $payload)]);

        SendPushNotification::dispatch(
            $this->tripParentsToken($trip->id),
            __('lang.trip_started'),
            $trip->name,
            ['view' => 'BusinessTrip', 'id' => $args['trip_id']]
        );

        Driver::updateLocation($args['latitude'], $args['longitude']);

        $this->broadcastTripStatus($trip, ['status' => 'STARTED', 'log_id' => $trip->log_id]);

        $trip->update(['starts_at' => $args['trip_time']]);

        return $trip;
    }

    public function atStation(array $args)
    {
        try { 
            SendPushNotification::dispatch(
                $this->stationParentsToken($args['trip_id'], $args['station_id']), 
                __('lang.captain_arrived'),
                $args['trip_name'],
                ['view' => 'BusinessTrip', 'id' => $args['trip_id']]
            );

            $payload = array([
                'station_id' => $args['station_id'],
                'station_name' => $args['station_name'],
                'status' => 'at station',
                'at' => date("Y-m-d H:i:s"),
                'eta' => $args['eta'],
                'lat' => $args['latitude'],
                'lng' => $args['longitude']
            ]);
            
            return $this->updateEventPayload($args['log_id'], $payload);

        } catch (\Exception $e) {
            throw new CustomException(__('lang.notify_station_failed'));
        }
    }

    public function changePickupStatus(array $args)
    {
        if (gettype($args['students']) == "string") {
            $args['students'] = json_decode($args['students'], true);
        }

        $student_ids = Arr::pluck($args['students'], 'id');

        $this->updateStudentStatus(
            $args['trip_id'], ['is_picked_up' => $args['is_picked_up']], $student_ids
        );

        $data = [
            'status' => $args['is_picked_up'] ? 'picked up' : 'dropped off',
            'at' => date("Y-m-d H:i:s"),
            'lat' => $args['latitude'],
            'lng' => $args['longitude'],
            'by' => 'user'
        ];

        foreach($args['students'] as $student) {
            $data['student_id'] = $student['id'];
            $data['student_name'] = $student['name'];
            $payload[] = $data;
        }
        
        return $this->updateEventPayload($args['log_id'], $payload);
    }

    public function changeAttendenceStatus(array $args)
    {
        if (gettype($args['students']) == "string") {
            $args['students'] = json_decode($args['students'], true);
        }

        $arr = [
            'date' => $args['date'],
            'trip_id' => $args['trip_id'],
            'is_absent' => $args['is_absent'],
            'created_at' => date('Y-m-d H:i:s'), 
            'updated_at' => date('Y-m-d H:i:s')
        ];

        foreach($args['students'] as $student) {
            $arr['student_id'] = $student['id'];
            $data[] = $arr;
        } 
        
        SchoolTripAttendence::upsert($data, ['date', 'trip_id', 'student_id']);
        
        $student_ids = Arr::pluck($args['students'], 'id');

        $this->updateStudentStatus(
            $args['trip_id'], ['is_absent' => $args['is_absent']], $student_ids
        );

        $this->attendenceNotification($args);

        $temp = [
            'user_id' => $args['user_id'],
            'user_name' => $args['user_name'],
            'status' => $args['is_absent'] ? 'absent' : 'present',
            'at' => date("Y-m-d H:i:s"),
            'lat' => $args['latitude'],
            'lng' => $args['longitude'],
            'by' => $args['by']
        ];

        foreach($args['students'] as $student) {
            $temp['student_id'] = $student['id'];
            $temp['student_name'] = $student['name'];
            $payload[] = $temp;
        }
        
        return $this->updateEventPayload($args['log_id'], $payload);
    }

    public function pickStudents(array $args)
    {
        $msg = __('lang.welcome_trip');

        if (gettype($args['students']) == "string") {
            $args['students'] = json_decode($args['students'], true);
        }

        return $this->pickOrDropStudents($args, true, $msg);
    }

    public function dropStudents(array $args)
    {
        $msg = __('lang.bye_trip');

        if (gettype($args['students']) == "string") {
            $args['students'] = json_decode($args['students'], true);
        }

        $this->createParentsRatings($args);
        
        return $this->pickOrDropStudents($args, false, $msg);
    }

    public function updateDriverLocation(array $args)
    {
        try {
            $input = [
                'log_id' => $args['log_id'],
                'latitude' => $args['latitude'],
                'longitude' => $args['longitude']
            ];
            Driver::updateLocation($args['latitude'], $args['longitude']);
            return SchoolTripEntry::create($input);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    public function end(array $args)
    {
        $trip = $this->getTripById($args['trip_id']);

        if (!$trip->log_id) 
            throw new CustomException(__('lang.trip_ended'));

        $logId = $trip->log_id;

        $trip->update(['log_id' => null, 'starts_at' => null, 'ready_at' => null]);

        $this->updateStudentStatus(
            $args['trip_id'],
            ['is_picked_up' => false, 'is_absent' => false, 'is_scheduled' => true]
        );

        return $this->closeTripEvent($args, $logId, $trip);
    }

    public function destroy(array $args)
    {
        return $this->model->whereIn('log_id', $args['log_id'])->delete();
    }

    protected function getTripById($id)
    {
        try {
            return SchoolTrip::select(
                'school_trips.id', 'school_trips.name', 
                'school_trips.log_id', 'drivers.id as driver_id',
                'drivers.name as driver_name',
                'drivers.latitude as driver_lat', 'drivers.longitude as driver_lng',
                'partners.id as partner_id', 'partners.name as partner_name',
                'vehicle_id',
                'supervisor_id'
            )
            ->join('drivers', 'drivers.id', '=', 'school_trips.driver_id')
            ->join('partners', 'partners.id', '=', 'school_trips.partner_id')
            ->findOrFail($id);
        } catch (\Exception $e) {
            throw new CustomException(__('lang.could_not_find_this_trip'));
        }
    }

    protected function pickOrDropStudents($args, $is_picked_up, $msg)
    {
        try {
            $student_ids = Arr::pluck($args['students'], 'id');

            $this->updateStudentStatus(
                $args['trip_id'], ['is_picked_up' => $is_picked_up], $student_ids
            );

            SendPushNotification::dispatch(
                $this->parentsToken($args['trip_id'], $student_ids), 
                $msg, 
                $args['trip_name'],
                ['view' => 'BusinessTripUserStatus', 'id' => $args['trip_id']]
            );

            $data = [
                'status' => $is_picked_up ? 'picked up' : 'dropped off',
                'at' => date("Y-m-d H:i:s"),
                'lat' => $args['latitude'], 
                'lng' => $args['longitude'],
                'by' => 'driver'
            ];

            foreach($args['students'] as $student) {
                $data['student_id'] = $student['id'];
                $data['student_name'] = $student['name'];
                $payload[] = $data;
            }

            return $this->updateEventPayload($args['log_id'], $payload);

        } catch (\Exception $e) {
            throw new CustomException(__('lang.change_student_status_failed'));
        }
    }

    protected function updateEventPayload($logId, $payload)
    {
        try {
            $event = $this->model->select('content', 'log_id')
                ->findOrFail($logId);
    
            if (array_key_exists('payload', $event->content)) 
                $payload = array_merge($event->content['payload'], $payload);
                
            return $event->update(['content' => array_merge($event->content, ['payload' => $payload])]);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function closeTripEvent($args, $logId, $trip)
    {
        try {
            $event = $this->model->select('content', 'log_id')
                ->findOrFail($logId);

            $locations = SchoolTripEntry::select('latitude', 'longitude')
                ->where('log_id', $logId)
                ->get();

            if ($locations->isNotEmpty()) {
                foreach($locations as $loc) 
                    $path[] = $loc->latitude.','.$loc->longitude;

                $updatedData['map_url'] = StaticMapUrl::generatePath(implode('|', $path));

                SchoolTripEntry::where('log_id', $logId)
                    ->delete();
            }

            $ended = ['at' => date("Y-m-d H:i:s")];

            if (array_key_exists('latitude', $args) && array_key_exists('longitude', $args)) {
                $ended['lat'] = $args['latitude'];
                $ended['lng'] = $args['longitude'];

                $this->broadcastTripStatus($trip, ['status' => 'ENDED', 'log_id' => null]);
            }

            $updatedData['content'] = array_merge($event->content, ['ended' => $ended]);

            return $event->update($updatedData);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function attendenceNotification($args)
    {
        try {
            $status_text = $args['is_absent'] ? 'Absent' : 'Present';

            switch($args['by']) {
                case 'user':
                    $token = $this->driverToken($args['driver_id']);

                    $msg = __('lang.attendence_changed', [
                            'user' => $args['user_name'],
                            'status' => $status_text,
                        ]);
                    break;
                default:
                    $token = $this->userToken($args['user_id']);
                    $msg = __('lang.captain_changed_attendence', 
                        ['status' => $status_text]);
            }

            SendPushNotification::dispatch(
                $token, 
                $msg, 
                $args['trip_name'],
                ['view' => 'BusinessTripUserStatus', 'id' => $args['trip_id']]
            );

        } catch(\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function checkAbsence($trip_id)
    {
        try {
            $absent_students = SchoolTripAttendence::whereAbsent($trip_id);
            
            if ($absent_students) 
                $this->updateStudentStatus($trip_id, ['is_absent' => true], $absent_students);
        } catch(\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function checkSchedule($trip_id)
    {
        try {
            $not_scheduled_students = SchoolTripSchedule::whereNotScheduled($trip_id);
            
            if ($not_scheduled_students) 
                $this->updateStudentStatus($trip_id, ['is_scheduled' => false], $not_scheduled_students);
        } catch(\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function initTripEvent($args, $logId, $driverId, $vehicleId, $supervisorId)
    {
        try {
            $input = [
                'trip_id' => $args['trip_id'],
                'trip_time' => $args['trip_time'],
                'driver_id' => $driverId,
                'supervisor_id' => $supervisorId,
                'vehicle_id' => $vehicleId,
                'log_id' => $logId,
                'content' => [ 
                    'ready' => [
                        'at' => date("Y-m-d H:i:s"),
                        'lat' => $args['latitude'],
                        'lng' => $args['longitude']
                    ]
                ]
            ];
            $this->model->create($input);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function broadcastTripStatus($trip, $input)
    {
        $data = [
            'id' => $trip->id,
            'log_id' => $input['log_id'],
            'name' => $trip->name,
            'status' => $input['status'],
            'partner' => [
                'id' => $trip->partner_id,
                'name' => $trip->partner_name,
                '__typename' => 'Partner'
            ],
            'driver' => [
                'id' => $trip->driver_id,
                'name' => $trip->driver_name,
                'latitude' => $trip->driver_lat,
                'longitude' => $trip->driver_lng,
                '__typename' => 'Driver'
            ],
            '__typename' => 'BusinessTrip'
        ];
        broadcast(new SchoolTripStatusChanged($data));
    }

    protected function createParentsRatings($args)
    {
        $student_ids = Arr::pluck($args['students'], 'id');

        $arr = [
            'trip_id' => $args['trip_id'],
            'log_id' => $args['log_id'],
            'trip_time' => $args['trip_time'],
            'driver_id' => $args['driver_id'],
            'created_at' => date('Y-m-d H:i:s'), 
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $user_ids = $this->getStudentsParents($student_ids);

        foreach($user_ids as $user_id) {
            $arr['user_id'] = $user_id;
            $data[] = $arr;
        }

        SchoolTripRating::insert($data);
    }
}