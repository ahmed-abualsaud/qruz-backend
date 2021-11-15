<?php

namespace App\Traits;

use App\User;
use App\Student;

trait HandleSchoolTripDeviceTokens
{
    use HandleFollowerTokens;

    protected function tripParentsToken($trip_id)
    {
        return $this->getSchoolTripParentsToken()
            ->where('school_trip_subscriptions.trip_id', $trip_id)
            ->pluck('device_id')
            ->merge($this->getSchoolTripFollowersTokens($trip_id))
            ->unique()
            ->toArray();
    }

    protected function stationParentsToken($trip_id, $station_id)
    {
        return $this->getSchoolTripParentsToken()
            ->where('school_trip_subscriptions.station_id', $station_id)
            ->pluck('device_id')
            ->merge($this->getSchoolTripFollowersTokens($trip_id))
            ->unique()
            ->toArray();
    }

    protected function parentsToken($trip_id, array $student_ids)
    {
        $parent_ids = $this->getStudentsParents($student_ids);

        return $this->getSchoolTripParentsToken()
            ->whereIn('school_trip_subscriptions.student_id', $student_ids)
            ->pluck('device_id')
            ->merge($this->getUsersFollowersTokens($trip_id, $parent_ids))
            ->unique()
            ->toArray();
    }

    protected function getSchoolTripParentsToken()
    {
        return User::select('device_id')
            ->Join('school_trip_subscriptions', 'school_trip_subscriptions.user_id', '=', 'users.id')
            ->where('school_trip_subscriptions.is_absent', false)
            ->where('school_trip_subscriptions.is_scheduled', true);
    }

    protected function getStudentsParents($student_ids)
    {
        return Student::whereIn('id', $student_ids)
            ->pluck('parent_id')
            ->unique()
            ->toArray();
    }
}