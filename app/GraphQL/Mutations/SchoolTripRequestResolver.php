<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolTripRequestRepositoryInterface;

class SchoolTripRequestResolver
{
    private $schoolTripRequestRepository;

    public function __construct(SchoolTripRequestRepositoryInterface $schoolTripRequestRepository)
    {
        $this->schoolTripRequestRepository = $schoolTripRequestRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function createTrip($_, array $args)
    {
        return $this->schoolTripRequestRepository->createTrip($args);
    }

    public function addToTrip($_, array $args)
    {
        return $this->schoolTripRequestRepository->addToTrip($args);
    }

}
