<?php

namespace App\Repository\Eloquent\Mutations;

use App\SchoolTrip;
use App\SchoolTripAttendence;
use App\Exceptions\CustomException;
use App\Traits\HandleSchoolTripStudentStatus;
use App\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Arr;

class SchoolTripAttendenceRepository extends BaseRepository
{
    use HandleSchoolTripStudentStatus;

    public function __construct(SchoolTripAttendence $model)
    {
        parent::__construct($model);
    }

    public function create(array $args)
    {
        try {   
            if ($args['date'] === date('Y-m-d')) {                
                $this->updateStudentStatus(
                    $args['trip_id'], 
                    ['is_absent' => $args['is_absent']], 
                    $args['student_id']
                );
            }

            $arr = [
                'date' => $args['date'],
                'trip_id' => $args['trip_id'],
                'is_absent' => $args['is_absent'],
                'created_at' => date('Y-m-d H:i:s'), 
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            foreach($args['student_id'] as $val) {
                $arr['student_id'] = $val;
                $data[] = $arr;
            } 
            
            return $this->model->upsert($data, ['date', 'trip_id', 'student_id']);

        } catch(\Exception $e) {
            throw new CustomException(__('lang.create_attendence_failed'));
        }
    }
}