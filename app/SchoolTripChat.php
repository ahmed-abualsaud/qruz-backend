<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolTripChat extends Model
{
    protected $guarded = [];

    public $table = 'school_trip_chat';

    public function sender()
    {
        return $this->morphTo();
    }
}
