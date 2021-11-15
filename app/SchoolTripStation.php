<?php

namespace App;

use App\Scopes\SortByOrderScope;
use Illuminate\Database\Eloquent\Model;

class SchoolTripStation extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SortByOrderScope);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'school_trip_subscriptions', 'station_id', 'student_id')
            ->whereNotNull('school_trip_subscriptions.subscription_verified_at');
    }


}
