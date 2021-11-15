<?php

namespace App\Repository\Eloquent\Queries;   

use App\SchoolTrip;
use App\SchoolTripEvent;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Queries\SchoolTripRepositoryInterface;

class SchoolTripRepository extends BaseRepository implements SchoolTripRepositoryInterface
{

    /**
    * SchoolTripRepository constructor.
    *
    * @param SchoolTrip $model
    */
    public function __construct(SchoolTrip $model)
    {
        parent::__construct($model);
    }

    public function studentSubscriptions(array $args)
    {
        $studentSubscriptions = $this->model->join('school_trip_subscriptions', 'school_trips.id', '=', 'school_trip_subscriptions.trip_id')
            ->where('school_trip_subscriptions.student_id', $args['student_id'])
            ->whereNotNull('school_trip_subscriptions.subscription_verified_at')
            ->selectRaw(
                'school_trips.id, school_trips.name, school_trips.name_ar,
                school_trip_subscriptions.due_date, 
                school_trip_subscriptions.payable, school_trip_subscriptions.id as subscription_id'
            )
            ->get();

        return $studentSubscriptions;
    }

    public function studentTrips(array $args)
    {
        $date = date('Y-m-d', strtotime($args['day']));

        $studentTrips = $this->model->selectRaw(
            'school_trips.id, school_trips.name, school_trips.name_ar, school_trips.days,
            school_trip_attendences.date AS absence_date'
        )
        ->join('school_trip_subscriptions', 'school_trips.id', '=', 'school_trip_subscriptions.trip_id')
        ->where('school_trip_subscriptions.student_id', $args['student_id'])
        ->whereNotNull('school_trip_subscriptions.subscription_verified_at')
        ->whereRaw('? between start_date and end_date', [date('Y-m-d')])
        ->whereRaw('JSON_EXTRACT(school_trips.days, "$.'.$args['day'].'") <> CAST("null" AS JSON)')
        ->where(function ($query) use ($args) {
            $query->whereNull('school_trip_schedules.days')
                ->orWhere('school_trip_schedules.days->'.$args['day'], true);
        })
        ->leftJoin('school_trip_attendences', function ($join) use ($args, $date) {
            $join->on('school_trips.id', '=', 'school_trip_attendences.trip_id')
                ->where('school_trip_attendences.student_id', $args['student_id'])
                ->where('school_trip_attendences.is_absent', true)
                ->where('school_trip_attendences.date', $date);
        })
        ->leftJoin('school_trip_schedules', function ($join) use ($args) {
            $join->on('school_trips.id', '=', 'school_trip_schedules.trip_id')
                ->where('school_trip_schedules.student_id', $args['student_id']);
        })
        ->get();

        if ($studentTrips->isEmpty()) return [];

        return $this->studentSchedule($studentTrips, $args['day']);
    }

    public function studentLiveTrips(array $args)
    {
        $today = strtolower(date('l'));

        return $this->model->selectRaw('school_trips.id, school_trips.name, school_trips.name_ar')
            ->join('school_trip_subscriptions', 'school_trips.id', '=', 'school_trip_subscriptions.trip_id')
            ->where('school_trip_subscriptions.student_id', $args['student_id'])
            ->whereNotNull('log_id')
            ->whereRaw('JSON_EXTRACT(school_trips.days, "$.'.$today.'") <> CAST("null" AS JSON)')
            ->where(function ($query) use ($today) {
                $query->whereNull('school_trip_schedules.days')
                    ->orWhere('school_trip_schedules.days->'.$today, true);
            })
            ->leftJoin('school_trip_schedules', function ($join) use ($args) {
                $join->on('school_trips.id', '=', 'school_trip_schedules.trip_id')
                    ->where('school_trip_schedules.student_id', $args['student_id']);
            })
            ->get();
    }

    public function driverTrips(array $args)
    {
        $driverTrips = $this->model->select('id', 'name', 'name_ar', 'days')
            ->where('driver_id', $args['driver_id'])
            ->whereRaw('? between start_date and end_date', [date('Y-m-d')])
            ->whereRaw('JSON_EXTRACT(days, "$.'.$args['day'].'") <> CAST("null" AS JSON)')
            ->get();

        if ($driverTrips->isEmpty()) return [];

        return $this->driverSchedule($driverTrips, $args['day']);
    }

    public function driverLiveTrips(array $args)
    {
        $liveTrips = $this->model->select('id', 'name', 'name_ar')
            ->where('driver_id', $args['driver_id'])
            ->whereNotNull('log_id')
            ->get();

        return $liveTrips;
    }

    public function studentHistory(array $args)
    {
        return SchoolTripEvent::selectRaw('
            school_trips.name AS trip_name,
            school_trip_events.*
        ')
        ->join('school_trip_subscriptions', 'school_trip_subscriptions.trip_id', '=', 'school_trip_events.trip_id')
        ->join('school_trips', 'school_trips.id', '=', 'school_trip_events.trip_id')
        ->where('student_id', $args['student_id'])
        ->latest('school_trip_events.created_at');        
    }

    protected function studentSchedule($trips, $day) 
    {
        $dateTime = date('Y-m-d', strtotime($day));
        
        foreach($trips as $trip) {
            $trip->is_absent = $trip->absence_date === $dateTime;
            $trip->starts_at = $dateTime.' '.$trip->days[$day];
        }
        
        return $trips->sortBy('starts_at')->values();
    }

    protected function driverSchedule($trips, $day) 
    {
        $dateTime = date('Y-m-d', strtotime($day));
        
        foreach($trips as $trip) {
            $trip->starts_at = $dateTime.' '.$trip->days[$day];
        }
        
        return $trips->sortBy('starts_at')->values();
    }
}