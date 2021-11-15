<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolTripRating extends Model
{

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function trip()
    {
        return $this->belongsTo(SchoolTrip::class);
    }

    public function scopeUnrated($query, $args) 
    {
        return $query->select(
            'school_trip_ratings.id', 
            'school_trips.name', 
            'school_trip_ratings.trip_time as starts_at'
            )
            ->join('school_trips', 'school_trip_ratings.trip_id', '=', 'school_trips.id')
            ->where('user_id', $args['user_id'])
            ->whereNull('rating');
    }
}
