<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolTripStationRepositoryInterface;

class SchoolTripStationResolver
{
    private $schoolTripStationRepository;

    public function  __construct(SchoolTripStationRepositoryInterface $schoolTripStationRepository)
    {
        $this->schoolTripStationRepository = $schoolTripStationRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function update($_, array $args)
    {
        return $this->schoolTripStationRepository->update($args);
    }

    public function assignStudent($_, array $args)
    {
        return $this->schoolTripStationRepository->assignStudent($args);
    }

    public function acceptStation($_, array $args)
    {
        return $this->schoolTripStationRepository->acceptStation($args);
    }

    public function destroy($_, array $args)
    {
        return $this->schoolTripStationRepository->destroy($args);
    }
}
