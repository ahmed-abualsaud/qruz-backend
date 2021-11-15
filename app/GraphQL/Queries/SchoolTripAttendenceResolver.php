<?php

namespace App\GraphQL\Queries;

use App\Repository\Queries\MainRepositoryInterface;

class SchoolTripAttendenceResolver
{

    private $schoolTripAttendenceRepository;
  
    public function __construct(MainRepositoryInterface $schoolTripAttendenceRepository)
    {
        $this->schoolTripAttendenceRepository = $schoolTripAttendenceRepository;
    }

    public function __invoke($_, array $args)
    {
        return $this->schoolTripAttendenceRepository->invoke($args);
    }
}
