<?php

namespace App;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolTrip extends Model
{
    use Searchable;
    use SoftDeletes;
    
    protected $guarded = [];

    protected $casts = [
        'days' => 'array'
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function stations() 
    {        
        $stations = $this->hasMany(SchoolTripStation::class, 'trip_id');

        if (auth('user')->user()) {
            $stations->selectRaw('
                school_trip_stations.*, 
                station.station_id AS is_my_station, 
                destination.destination_id AS is_my_destination
            ')
            ->leftJoin('school_trip_subscriptions as station', function ($join) {
                $join->on('station.station_id', '=', 'school_trip_stations.id')
                    ->where('station.user_id', auth('user')->id());
            })
            ->leftJoin('school_trip_subscriptions as destination', function ($join) {
                $join->on('destination.destination_id', '=', 'school_trip_stations.id')
                    ->where('destination.user_id', auth('user')->id());
            });
        }

        return $stations->whereNotNull('accepted_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_trip_subscriptions', 'trip_id', 'user_id')
            ->whereNotNull('school_trip_subscriptions.subscription_verified_at');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'school_trip_subscriptions', 'trip_id', 'student_id')
            ->whereNotNull('school_trip_subscriptions.subscription_verified_at');
    }

    public function scopeLive($query) 
    {
        return $query->whereNotNull('log_id')
            ->whereNotNull('starts_at');
    }

    public function scopePartner($query, $args) 
    {
        if (array_key_exists('partner_id', $args) && $args['partner_id']) {
            $query->where('partner_id', $args['partner_id']);
        }
 
        return $query;
    }

    public function scopeUnready($query, $args) 
    {
        $day = strtolower(date('l', strtotime($args['date'])));
        
        return $query
            ->selectRaw('
                school_trips.id, 
                school_trips.name, 
                school_trips.driver_id, 
                school_trips.supervisor_id, 
                school_trips.days
            ')
            ->whereNull('event.log_id')
            ->whereRaw('? between start_date and end_date', [date('Y-m-d')])
            ->whereRaw('days->"$.'.$day.'" <> CAST("null" AS JSON)')
            ->leftJoin('school_trip_events as event', function ($join) use ($args) {
                $join->on('event.trip_id', '=', 'school_trips.id')
                    ->whereDate('event.trip_time', $args['date']);
            });
    }

    public function scopeSearch($query, $args) 
    {
        if (array_key_exists('searchQuery', $args) && $args['searchQuery']) {
            $query = $this->search($args['searchFor'], $args['searchQuery'], $query);
        }

        return $query->latest();
    }
    
}
