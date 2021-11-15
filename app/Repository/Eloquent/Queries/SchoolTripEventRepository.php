<?php

namespace App\Repository\Eloquent\Queries;   

use App\Traits\Searchable;
use App\Traits\Filterable;
use App\SchoolTripEvent;
use App\Repository\Eloquent\BaseRepository;

class SchoolTripEventRepository extends BaseRepository
{
    use Searchable, Filterable;

    public function __construct(SchoolTripEvent $model)
    {
        parent::__construct($model);
    }

    public function index(array $args)
    {
        if (array_key_exists('trip_id', $args) && $args['trip_id']) {
            $events = $this->model->selectRaw('
                drivers.id AS driver_id, drivers.name AS driver_name, school_trip_events.*
            ')
            ->where('trip_id', $args['trip_id']);
        } else {
            $events = $this->model->selectRaw('
                school_trips.id AS trip_id, school_trips.name AS trip_name,
                school_trips.duration AS trip_duration, school_trips.distance AS trip_distance,
                drivers.id AS driver_id, drivers.name AS driver_name,
                school_trip_events.*
            ')
            ->join('school_trips', 'school_trips.id', '=', 'school_trip_events.trip_id');

            if (array_key_exists('partner_id', $args) && $args['partner_id']) {
                $events = $events->where('school_trips.partner_id', $args['partner_id']);
            }

            if (array_key_exists('type', $args) && $args['type']) {
                $events = $events->where('school_trips.type', $args['type']);
            }
        }

        if (array_key_exists('searchQuery', $args) && $args['searchQuery']) {
            $events = $this->search($args['searchFor'], $args['searchQuery'], $events);
        }

        if (array_key_exists('period', $args) && $args['period']) {
            $events = $this->dateFilter($args['period'], $events, 'school_trip_events.created_at');
        }

        return $events->join('drivers', 'drivers.id', '=', 'school_trip_events.driver_id')
            ->latest('school_trip_events.created_at');
    }
}