<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $guarded = [];

    public $table = 'school_trip_followers';

    public function trip()
    {
        return $this->belongsTo(SchoolTrip::class);
    }

    public function scopeTrip($query, $args)
    {
        return $query->join('school_trips', 'school_trip_followers.trip_id', '=', 'school_trips.id')
            ->where('follower_id', $args['follower_id']);
    }

    public function scopeFollower($query, $args)
    {
        return $query->select('school_trip_followers.id', 'name', 'avatar', 'trip_id')
            ->join('users', 'school_trip_followers.user_id', '=', 'users.id')
            ->where('user_id', $args['user_id']);
    }
}
