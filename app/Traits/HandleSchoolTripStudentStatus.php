<?php

namespace App\Traits;

use App\SchoolTripSubscription;

trait HandleSchoolTripStudentStatus
{
    protected function updateStudentStatus($trip_id, $status, $students = null)
    {
        $studentsStatus = SchoolTripSubscription::where('trip_id', $trip_id);

        if ($students) {
            if (is_array(($students))) {
                $studentsStatus->whereIn('student_id', $students);
            } else {
                $studentsStatus->where('student_id', $students);
            }
        }

        return $studentsStatus->update($status);
    }
}