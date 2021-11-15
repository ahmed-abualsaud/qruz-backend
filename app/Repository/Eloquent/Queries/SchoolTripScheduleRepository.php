<?php

namespace App\Repository\Eloquent\Queries;   

use App\SchoolTripSchedule;
use App\Exceptions\CustomException;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Queries\MainRepositoryInterface;

class SchoolTripScheduleRepository extends BaseRepository implements MainRepositoryInterface
{

   public function __construct(SchoolTripSchedule $model)
   {
        parent::__construct($model);
   }

   public function invoke(array $args)
   {
        try {
            return $this->model->select('days')
                ->where('trip_id', $args['trip_id'])
                ->where('student_id', $args['student_id'])
                ->firstOrFail();
        } catch(\Exception $e) {
            throw new CustomException(__('lang.no_schedule'));
        }
   }
}