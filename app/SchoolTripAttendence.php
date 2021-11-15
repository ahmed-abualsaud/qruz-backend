<?php

namespace App;

use App\Traits\HandleUpdateOrInsert;
use Illuminate\Database\Eloquent\Model;

class SchoolTripAttendence extends Model
{
    use HandleUpdateOrInsert;
    protected $guarded = [];

    public static function upsert(array $rows, array $update)
    {
        return self::updateOrInsert(
            (new self())->getTable(),
            $rows,
            $update
        );
    }

    public function scopeWhereAbsent($query, $trip_id)
    {
        return $query->select('student_id')
            ->where('trip_id', $trip_id)
            ->where('date', date('Y-m-d'))
            ->where('is_absent', true)
            ->pluck('student_id')
            ->toArray();
    }
}
