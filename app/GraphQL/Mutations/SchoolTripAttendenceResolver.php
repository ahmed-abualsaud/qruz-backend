<?php

namespace App\GraphQL\Mutations;

use App\Repository\Eloquent\Mutations\SchoolTripAttendenceRepository;

class SchoolTripAttendenceResolver
{
    private $schoolTripAttendenceRepository;

    public function __construct(SchoolTripAttendenceRepository $schoolTripAttendenceRepository)
    {
        $this->schoolTripAttendenceRepository = $schoolTripAttendenceRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function create($_, array $args)
    {
        return $this->schoolTripAttendenceRepository->create($args);
    }

}
