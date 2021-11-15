<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolTripScheduleRepositoryInterface;


class SchoolTripScheduleResolver
{
    private $schoolTripScheduleRepository;

    public function  __construct(SchoolTripScheduleRepositoryInterface $schoolTripScheduleRepository)
    {
        $this->schoolTripScheduleRepository = $schoolTripScheduleRepository;
    }
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function reschedule($_, array $args)
    {
        return $this->schoolTripScheduleRepository->reschedule($args);
    }
}
