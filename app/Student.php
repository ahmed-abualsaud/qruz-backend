<?php

namespace App;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use Searchable;
    
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function scopeSearch($query, $args) 
    {
        if (array_key_exists('searchQuery', $args) && $args['searchQuery']) {
            $query = $this->search($args['searchFor'], $args['searchQuery'], $query);
        }
        return $query->latest();
    }

    public function scopeUnsubscribed($query, $args) 
    {
        $schoolTripsStudents = SchoolTripSubscription::select('student_id')
            ->where('trip_id', $args['trip_id']);

        $query->select('id', 'name', 'avatar', 'phone');

        if (array_key_exists('partner_id', $args) && $args['partner_id']) {
            $query->join('partner_users', 'students.parent_id', '=', 'partner_users.user_id')
                ->where('partner_users.partner_id', $args['partner_id'])
                ->whereNotIn('students.id', $schoolTripsStudents);
        } else {
            $query->whereNotIn('id', $schoolTripsStudents);
        }

        return $query;
    }
}