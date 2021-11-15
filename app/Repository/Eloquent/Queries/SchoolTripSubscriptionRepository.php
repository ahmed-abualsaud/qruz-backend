<?php

namespace App\Repository\Eloquent\Queries;   

use App\Student;
use App\SchoolTripSubscription;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Queries\SchoolTripSubscriptionRepositoryInterface;
use Illuminate\Support\Collection;

class SchoolTripSubscriptionRepository extends BaseRepository implements SchoolTripSubscriptionRepositoryInterface
{
    private $student;

    /**
    * SchoolTripSubscriptionRepository constructor.
    *
    * @param SchoolTripSubscription,Student $model
    */
    public function __construct(SchoolTripSubscription $model, Student $student)
    {
        parent::__construct($model);
        $this->student = $student;
    }
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function schoolTripSubscribedStudents(array $args): Collection
    {
        $students = $this->student->selectRaw(
            'students.id, students.name, students.avatar, students.phone, 
            station.id AS station_id, station.name AS station_name, 
            destination.id AS destination_id, destination.name AS destination_name, 
            subscription.subscription_verified_at, subscription.payable, subscription.due_date'
        )
        ->join(
            'school_trip_subscriptions as subscription', 
            'subscription.student_id', '=', 'students.id'
        )
        ->leftJoin(
            'school_trip_stations as station', 
            'station.id', '=', 'subscription.station_id'
        )
        ->leftJoin(
            'school_trip_stations as destination', 
            'destination.id', '=', 'subscription.destination_id'
        )
        ->where('subscription.trip_id', $args['trip_id'])
        ->get();

        return $students;
    }

    public function schoolTripStationStudents(array $args): Collection
    {
        $students = $this->student->select('students.id', 'students.name', 'students.avatar', 'students.phone')
            ->join('school_trip_subscriptions', 'school_trip_subscriptions.student_id', '=', 'students.id');

            if ($args['status'] == 'assigned') {
                $students = $students->where('station_id', $args['station_id'])
                    ->orWhere('destination_id', $args['station_id'])
                    ->addSelect(DB::raw('
                        (CASE 
                            WHEN station_id = '.$args['station_id'].' 
                            THEN "pickup" ELSE "dropoff"
                            END
                        ) AS station_type
                    '));
            } else {
                $students = $students->where('trip_id', $args['trip_id'])
                    ->where(function ($query) use ($args) {
                        $query->whereNull('station_id')
                            ->orWhere('station_id', '<>', $args['station_id']);
                });
            }

        return $students->get();
    }

    public function schoolTripSubscribers(array $args): Collection
    {
        $students = $this->student->select('students.id', 'students.name', 'students.phone')
            ->join('school_trip_subscriptions', 'students.id', '=', 'school_trip_subscriptions.student_id')
            ->where('school_trip_subscriptions.trip_id', $args['trip_id'])
            ->where('school_trip_subscriptions.is_scheduled', true)
            ->where('school_trip_subscriptions.is_absent', false)
            ->whereNotNull('school_trip_subscriptions.subscription_verified_at');

            $students = $this->studentsByStatus($args, $students);

        return $students->get();
    }

    public function schoolTripStudentsStatus(array $args): Collection
    {
        $students = $this->student->selectRaw('students.id, students.name, students.phone, students.avatar, school_trip_subscriptions.is_picked_up, school_trip_subscriptions.is_absent')
            ->join('school_trip_subscriptions', 'students.id', '=', 'school_trip_subscriptions.student_id');

            if (array_key_exists('trip_id', $args) && $args['trip_id']) {
                $students = $students->where('school_trip_subscriptions.trip_id', $args['trip_id']);
            }
            
            if (array_key_exists('station_id', $args) && $args['station_id']) {
                $students = $students->where('school_trip_subscriptions.station_id', $args['station_id']);
            }

        return $students->get();
    }

    public function schoolTripStudentStatus(array $args)
    {
        try {
            $status = $this->model->select('is_absent', 'is_picked_up')
                ->where('trip_id', $args['trip_id'])
                ->where('student_id', $args['student_id'])
                ->firstOrFail();
        } catch (\Exception $e) {
            throw new CustomException(__('lang.get_student_status_failed'));
        }

        return $status;
    }

    protected function studentsByStatus($args, $students)
    {
        switch($args['status']) {
            case 'PICK_UP':
                $students = $students->where('school_trip_subscriptions.is_picked_up', false);
                if (array_key_exists('station_id', $args) && $args['station_id'])
                    $students = $students->where('station_id', $args['station_id']);

            break;
            case 'DROP_OFF':
                $students = $students->where('school_trip_subscriptions.is_picked_up', true);
                    if (array_key_exists('station_id', $args) && $args['station_id'])
                        $students = $students->where('destination_id', $args['station_id']);

            break;
            default:
                $students = $students;
        }

        return $students;
    }
}
