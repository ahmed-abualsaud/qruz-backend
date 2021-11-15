<?php

namespace App\Traits;

use App\Follower;

trait HandleFollowerTokens
{
    protected function getSchoolTripFollowersTokens($trip_id)
    {
        return Follower::Join('users', 'users.id', '=', 'school_trip_followers.follower_id')
            ->where('trip_id', $trip_id)
            ->pluck('device_id')
            ->toArray();
    }

    protected function getUsersFollowersTokens($trip_id, array $user_id)
    {
        return Follower::Join('users', 'users.id', '=', 'school_trip_followers.follower_id')
            ->where('trip_id', $trip_id)
            ->whereIn('user_id', $user_id)
            ->pluck('device_id')
            ->toArray();
    }
}