<?php

namespace App\Repository\Eloquent\Queries;   

use App\Student;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Queries\MainRepositoryInterface;

class SchoolTripAttendenceRepository extends BaseRepository implements MainRepositoryInterface
{
   public function __construct(Student $model)
   {
        parent::__construct($model);
   }

   public function invoke(array $args)
   {
        $students = $this->model->select('students.id', 'students.name', 'students.phone', 'students.avatar')
        ->join('school_trip_subscriptions', 'students.id', '=', 'school_trip_subscriptions.student_id')
        ->where('school_trip_subscriptions.trip_id', $args['trip_id']);

        if (array_key_exists('date', $args) && $args['date']) {
            $students = $students->leftJoin('school_trip_attendences', function ($join) use ($args) {
                $join->on('school_trip_attendences.student_id', '=', 'students.id')
                    ->where('school_trip_attendences.trip_id', $args['trip_id'])
                    ->where('school_trip_attendences.date', $args['date']);
                })
                ->addSelect('school_trip_attendences.is_absent', 'school_trip_attendences.comment');
        } else {
            $students = $students->where('school_trip_subscriptions.is_scheduled', true)
                ->where('school_trip_subscriptions.is_picked_up', false)
                ->whereNotNull('school_trip_subscriptions.subscription_verified_at')
                ->addSelect('school_trip_subscriptions.is_absent');
        }

        return $students->get();
   }
}