<?php

namespace App\Repository\Eloquent\Mutations;

use App\Student;
use App\SchoolTrip;
use App\SchoolRequest;
use App\SchoolTripSubscription;
use Illuminate\Support\Arr;
use App\SchoolTripStation;
use App\SchoolTripSchedule;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Repository\Mutations\SchoolTripRequestRepositoryInterface;

class SchoolTripRequestRepository implements SchoolTripRequestRepositoryInterface
{
    private $schoolTrip;
    private $schoolTripSubscription;
    private $schoolTripStation;
    private $schoolTripSchedule;

    public function __construct(SchoolTrip $schoolTrip, 
    SchoolTripSubscription $schoolTripSubscription,
    SchoolTripStation $schoolTripStation, SchoolTripSchedule $schoolTripSchedule)
    {
        $this->schoolTrip = $schoolTrip;
        $this->schoolTripSubscription = $schoolTripSubscription;
        $this->schoolTripStation = $schoolTripStation;
        $this->schoolTripSchedule = $schoolTripSchedule;
    }

    public function createTrip(array $args)
    {
        DB::beginTransaction();
        try {
            $input = Arr::except($args, ['directive', 'request_ids', 'destinations', 'students', 'user_id']);
            $schoolTrip = $this->createSchoolTrip($input);
            $this->createStationsAndDestinations($args, $schoolTrip->id);
            $this->assignStudentsToStationsAndDestinations($args, $schoolTrip->id);
            $this->createScheduleForEachStudent($args['students'], $schoolTrip->id);
            SchoolRequest::accept($args['request_ids']);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw new CustomException(__('lang.create_trip_failed'));
        }
    }

    public function addToTrip(array $args)
    {
        DB::beginTransaction();
        try {
            if (array_key_exists('station_id', $args) && array_key_exists('destination_id', $args)) {
                $this->assignStudentsToStationAndDestination($args);
            } else if (array_key_exists('station_id', $args)) {
                $this->createDestinations($args);
                $this->assignStudentsToDestinations($args);
                $this->assignStudentsToStation($args);
            } else if (array_key_exists('destination_id', $args)) {
                $this->createStations($args);
                $this->assignStudentsToStations($args);
                $this->assignStudentsToDestination($args);
            } else {
                $this->createStationsAndDestinations($args, $args['trip_id']);
                $this->assignStudentsToStationsAndDestinations($args, $args['trip_id']);
            }

            $this->updateTripSchedule($args);
            $this->createScheduleForEachStudent($args['students'], $args['trip_id']);
            SchoolRequest::accept($args['request_ids']);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw new CustomException(__('lang.add_request_failed').$e->getMessage());
        }
    }

    protected function assignStudentsToStationAndDestination(array $args)
    {
        try {
            
            $data = $this->stationsData($args);

            $this->schoolTripSubscription->upsert(
                $data, ['station_id', 'destination_id', 'request_id']
            );

        } catch(\Exception $e) {
            throw new CustomException(__('lang.assign_student_station_failed'));
        }
    }

    protected function assignStudentsToStation(array $args)
    {
        try {
            
            $data = $this->stationsData($args);

            $this->schoolTripSubscription->upsert(
                $data, ['station_id', 'request_id']
            );

        } catch(\Exception $e) {
            throw new CustomException(__('lang.assign_student_station_failed'));
        }
    }

    protected function assignStudentsToDestination(array $args)
    {
        try {
            
            $data = $this->stationsData($args);

            $this->schoolTripSubscription->upsert(
                $data, ['destination_id', 'request_id']
            );

        } catch(\Exception $e) {
            throw new CustomException(__('lang.assign_student_station_failed'));
        }
    }

    protected function assignStudentsToStationsAndDestinations(array $args, int $trip_id)
    {
        $arr = $this->subscriptionData($args, $trip_id);

        $stations = $this->stationsByTrip($trip_id);

        foreach($args['students'] as $student) {
            $arr['student_id'] = $student['id'];
            $arr['station_id'] = $stations->firstWhere('request_id', $student['request_id'])->id;
            $arr['destination_id'] = $stations->firstWhere('name', $student['destination'])->id;
            $arr['request_id'] = $student['request_id'];
            $data[] = $arr;
        }

        $this->schoolTripSubscription->insert($data);
    }

    protected function assignStudentsToStations(array $args)
    {
        $arr = $this->subscriptionData($args, $args['trip_id']);

        $stations = $this->stationsByTrip($args['trip_id']);

        foreach($args['students'] as $student) {
            $arr['student_id'] = $student['id'];
            $arr['station_id'] = $stations->firstWhere('request_id', $student['request_id'])->id;
            $arr['request_id'] = $student['request_id'];
            $data[] = $arr;
        }

        $this->schoolTripSubscription->insert($data);
    }

    protected function assignStudentsToDestinations(array $args)
    {
        $arr = $this->subscriptionData($args, $args['trip_id']);

        $stations = $this->stationsByTrip($args['trip_id']);

        foreach($args['students'] as $student) {
            $arr['student_id'] = $student['id'];
            $arr['destination_id'] = $stations->firstWhere('name', $student['destination'])->id;
            $arr['request_id'] = $student['request_id'];
            $data[] = $arr;
        }

        $this->schoolTripSubscription->insert($data);
    }

    protected function createStationsAndDestinations(array $args, int $trip_id)
    {
        $studentsData = $this->studentsData($args, $trip_id);
        $destinationsData = $this->destinationsData($args['destinations'], $trip_id);
        
        $this->schoolTripStation->insert(array_merge($studentsData, $destinationsData));
    }

    protected function createStations(array $args)
    {
        $studentsData = $this->studentsData($args, $args['trip_id']);
        
        $this->schoolTripStation->insert($studentsData);
    }

    protected function createDestinations(array $args)
    {
        $destinationsData = $this->destinationsData($args['destinations'], $args['trip_id']);
        
        $this->schoolTripStation->insert($destinationsData);
    }
    

    protected function createScheduleForEachStudent(array $students, int $trip_id)
    {
        $tripScheduleArr = [
            'trip_id' => $trip_id
        ];

        foreach($students as $student) {
            $tripScheduleArr['student_id'] = $student['id'];
            $tripScheduleArr['days'] = json_encode($student['days']);
            $tripScheduleData[] = $tripScheduleArr;
        }

        $this->schoolTripSchedule->upsert($tripScheduleData, ['days']);
    }

    protected function createSchoolTrip(array $input)
    {
        $schoolTrip = $this->schoolTrip->create($input);
        $schoolTrip->update(['subscription_code' => Hashids::encode($schoolTrip->id)]);

        return $schoolTrip;
    }

    protected function studentsData(array $args, int $trip_id)
    {
        $pickable = [
            'state' => 'PICKABLE',
            'trip_id' => $trip_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'accepted_at' => date('Y-m-d H:i:s'),
        ];
        foreach($args['students'] as $student) {
            $pickable['request_id'] = $student['request_id'];
            $pickable['name'] = $student['address'];
            $pickable['name_ar'] = $student['address'];
            $pickable['latitude'] = $student['lat'];
            $pickable['longitude'] = $student['lng'];
            $data[] = $pickable;
        }

        return $data;
    }

    protected function destinationsData(array $destinations, int $trip_id)
    {
        $arr = [
            'request_id' => null,
            'state' => 'DESTINATION',
            'trip_id' => $trip_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'accepted_at' => date('Y-m-d H:i:s'),
        ];
        foreach($destinations as $destination) {
            $arr['name'] = $destination['name'];
            $arr['name_ar'] = $destination['name'];
            $arr['latitude'] = $destination['lat'];
            $arr['longitude'] = $destination['lng'];
            $data[] = $arr;
        } 

        return $data;
    }

    protected function stationsData(array $args)
    {
        $arr = [
            'user_id' => $args['user_id'],
            'trip_id' => $args['trip_id'],
            'subscription_verified_at' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'), 
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (array_key_exists('station_id', $args)) {
            $arr['station_id'] = $args['station_id'];
        }

        if (array_key_exists('destination_id', $args)) {
            $arr['destination_id'] = $args['destination_id'];
        }
        
        foreach($args['students'] as $student) {
            $arr['student_id'] = $student['id'];
            $arr['request_id'] = $student['request_id'];
            $data[] = $arr;
        } 

        return $data;
    }

    protected function subscriptionData(array $args, int $trip_id)
    {
        return [
            'user_id' => $args['user_id'],
            'trip_id' => $trip_id,
            'subscription_verified_at' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'), 
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    protected function stationsByTrip(int $trip_id)
    {
        return $this->schoolTripStation->select('id', 'request_id', 'name')
            ->where('trip_id', $trip_id)
            ->get();
    }

    protected function updateTripSchedule(array $args)
    {
        $schedule = $this->schoolTrip->select('days')
            ->findOrFail($args['trip_id']);

        $this->schoolTrip->where('id', $args['trip_id'])
            ->update(['days' => array_merge($schedule->days, $args['days'])]);
    }
}
