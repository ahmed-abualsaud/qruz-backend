<?php

namespace App\GraphQL\Queries;

use App\Repository\Queries\SchoolTripRepositoryInterface;

class SchoolTripResolver
{
    private $schoolTripRepository;
  
    public function __construct(SchoolTripRepositoryInterface $schoolTripRepository)
    {
        $this->schoolTripRepository = $schoolTripRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function studentSubscriptions($_, array $args)
    {
        return $this->schoolTripRepository->studentSubscriptions($args);
    }

    public function studentTrips($_, array $args)
    {
        return $this->schoolTripRepository->studentTrips($args);
    }

    public function studentLiveTrips($_, array $args)
    {
        return $this->schoolTripRepository->studentLiveTrips($args);
    }

    public function driverTrips($_, array $args)
    {
        return $this->schoolTripRepository->driverTrips($args);
    }

    public function driverLiveTrips($_, array $args)
    {
        return $this->schoolTripRepository->driverLiveTrips($args);
    }

    public function studentHistory($_, array $args)
    {
        return $this->schoolTripRepository->studentHistory($args);
    }
}