<?php

namespace App;

use App\Traits\HandleUpdateOrInsert;
use Illuminate\Database\Eloquent\Model;

class SchoolTripSchedule extends Model
{
    use HandleUpdateOrInsert;

    protected $guarded = [];

    protected $primaryKey = ['trip_id', 'student_id'];

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'days' => 'array'
    ];

    public function scopeWhereNotScheduled($query, $trip_id)
    {
        return $query->select('student_id')
            ->where('trip_id', $trip_id)
            ->where('days->'.strtolower(date('l')), false)
            ->pluck('student_id')
            ->toArray();
    }

    public static function upsert(array $rows, array $update)
    {
        return self::updateOrInsert(
            (new self())->getTable(),
            $rows,
            $update
        );
    }
}
