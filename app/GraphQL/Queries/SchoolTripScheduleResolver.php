<?php

namespace App\GraphQL\Queries;

use App\Repository\Queries\MainRepositoryInterface;

class SchoolTripScheduleResolver
{

    private $schoolTripScheduleRepository;
  
    public function __construct(MainRepositoryInterface $schoolTripScheduleRepository)
    {
        $this->schoolTripScheduleRepository =  $schoolTripScheduleRepository;
    }

    public function __invoke($_, array $args)
    {
        return $this->schoolTripScheduleRepository->invoke($args);
    }
}
