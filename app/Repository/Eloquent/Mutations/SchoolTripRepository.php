<?php

namespace App\Repository\Eloquent\Mutations;

use App\User;
use App\SchoolTrip;
use App\Jobs\SendOtp;
use App\SchoolTripSubscription;
use Illuminate\Support\Arr;
use App\SchoolTripStation;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Mutations\SchoolTripRepositoryInterface;

class SchoolTripRepository extends BaseRepository implements SchoolTripRepositoryInterface
{

    public function __construct(SchoolTrip $model)
    {
        parent::__construct($model);
    }
    
    public function create(array $args)
    {
        DB::beginTransaction();
        try {
            $input = Arr::except($args, ['directive']);
            $schoolTrip = $this->createSchoolTrip($input);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw new CustomException(__('lang.create_trip_failed'));
        }

        return $schoolTrip;
    }

    public function update(array $args)
    {
        try {
            $tripInput = Arr::except($args, ['directive']);
            $trip = $this->model->findOrFail($args['id']);
            $trip->update($tripInput);
        } catch (ModelNotFoundException $e) {
            throw new CustomException(__('lang.trip_not_found'));
        }

        return $trip;
    }

    public function updateRoute(array $args)
    {
        try {
            
            $cases = []; $ids = []; $distance = []; $duration = []; $order = [];

            foreach ($args['stations'] as $value) {
                $id = (int) $value['id'];
                $cases[] = "WHEN {$id} then ?";
                $distance[] = $value['distance'];
                $duration[] = $value['duration'];
                $order[] = $value['order'];
                $ids[] = $id;
            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);
            $params = array_merge($distance, $duration, $order);

            DB::update("UPDATE `school_trip_stations` SET 
                `distance` = CASE `id` {$cases} END, 
                `duration` = CASE `id` {$cases} END, 
                `order` = CASE `id` {$cases} END
                WHERE `id` in ({$ids})", $params);

            $total = end($args['stations']);

            $this->model->where('id', $args['trip_id'])
                ->update([
                    'route' => $args['route'], 
                    'distance' => $total['distance'], 
                    'duration' => $total['duration']
                ]);
            
            return ['distance' => $total['distance'], 'duration' => $total['duration']];
            
        } catch (\Exception $e) {
            throw new CustomException(__('lang.update_route_failed'));
        }
    }

    public function copy(array $args)
    {
        DB::beginTransaction();
        try {
            $trip = $this->createTripCopy($args);

            if ($args['include_stations'])
                $this->createStationsCopy($args['id'], $trip->id);

            if ($args['include_subscriptions'])
                $this->createSubscriptionsCopy($args['id'], $trip->id);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw new CustomException($e->getMessage());
        }

        return $trip;
    }

    public function createSubscription(array $args)
    {
        try {
            $arr = [
                'trip_id' => $args['trip_id'],
                'station_id' => $args['station_id'],
                'destination_id' => $args['destination_id'],
                'created_at' => date('Y-m-d H:i:s'), 
                'updated_at' => date('Y-m-d H:i:s'),
                'subscription_verified_at' => date('Y-m-d H:i:s'),
                'payable' => $args['payable'],
                'user_id' => $args['user_id']
            ];

            if (array_key_exists('due_date', $args) && $args['due_date']) {
                $arr['due_date'] = $args['due_date'];
            } else {
                $arr['due_date'] = date('Y-m-d');
            }

            foreach($args['student_id'] as $val) {
                $arr['student_id'] = $val;
                $data[] = $arr;
            } 

            return SchoolTripSubscription::upsert($data, ['station_id', 'destination_id', 'payable', 'due_date', 'updated_at']);
        } catch (\Exception $e) {
            throw new CustomException(__('lang.subscribe_student_failed'));
        }
    }

    public function confirmSubscription(array $args) 
    {
        try {
            $trip_id = Hashids::decode($args['subscription_code']);
            $trip = $this->model->findOrFail($trip_id[0]);
        } catch (\Exception $e) {
            throw new CustomException(__('lang.subscription_code_is_not_valid'));
        }
        
        try {
            $tripStudent = SchoolTripSubscription::where('trip_id', $trip->id)
                ->where('student_id', $args['student_id'])
                ->firstOrFail();
            if ($tripStudent->subscription_verified_at) {
                throw new CustomException(__('lang.already_subscribed'));
            } else {
                $tripStudent->update([
                    'subscription_verified_at' => date('Y-m-d H:i:s'),
                    'payable' => $trip->price,
                    'due_date' => date('Y-m-d')
                ]);
            }
        } catch (ModelNotFoundException $e) {
            SchoolTripSubscription::create([
                'trip_id' => $trip->id,
                'student_id' => $args['student_id'],
                'user_id' => $args['user_id'],
                'subscription_verified_at' => date('Y-m-d H:i:s'),
                'due_date' => date('Y-m-d')
            ]);
        }
        
        return $trip;
    }

    public function deleteSubscription(array $args)
    {
        try {
            return SchoolTripSubscription::where('trip_id', $args['trip_id'])
                ->whereIn('student_id', $args['student_id'])
                ->delete();

        } catch (\Exception $e) {
            throw new CustomException(__('lang.cancel_subscribe_failed'));
        }
    }

    public function verifySubscription(array $args)
    {
        try {
            return SchoolTripSubscription::where('trip_id', $args['trip_id'])
                ->where('student_id', $args['student_id'])
                ->update(['subscription_verified_at' => $args['subscription_verified_at']]);
        } catch (\Exception $e) {
            throw new CustomException(__('lang.toggle_subscribe_failed'));
        }
    }

    protected function createSchoolTrip($input)
    {
        $schoolTrip = $this->model->create($input);
        $schoolTrip->update(['subscription_code' => Hashids::encode($schoolTrip->id)]);

        return $schoolTrip;
    }

    protected function createTripCopy(array $args)
    {
        $originalTrip = $this->model->select(
            'partner_id', 'driver_id', 'supervisor_id', 'vehicle_id', 'start_date', 'end_date', 'return_time', 
            'days', 'duration', 'distance', 'group_chat', 'route', 'price'
            )
            ->findOrFail($args['id'])
            ->toArray();

        $originalTrip['name'] = $args['name'];
        $originalTrip['name_ar'] = $args['name_ar'];
        
        return $this->createSchoolTrip($originalTrip);
    }

    protected function createStationsCopy($oldTripId, $newTripId)
    {
        $originalStations = SchoolTripStation::select(
            'name', 'name_ar', 'latitude', 'longitude', 'duration', 'distance', 'state', 'accepted_at', 'order'
            )
            ->where('trip_id', $oldTripId)
            ->get();

        foreach($originalStations as $station) {
            $station->trip_id = $newTripId;
        }

        return SchoolTripStation::insert($originalStations->toArray());
    }

    protected function createSubscriptionsCopy($oldTripId, $newTripId)
    {
        $originalSubscriptions = SchoolTripSubscription::select('user_id', 'student_id')
            ->where('trip_id', $oldTripId)
            ->get();

        foreach($originalSubscriptions as $subscription) {
            $subscription->trip_id = $newTripId;
            $subscription->subscription_verified_at = date('Y-m-d H:i:s');
            $subscription->due_date = date('Y-m-d');
        }

        return SchoolTripSubscription::insert($originalSubscriptions->toArray());
    }
}

