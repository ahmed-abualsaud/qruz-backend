<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolTripEventRepositoryInterface;

class SchoolTripEventResolver
{
    private $schoolTripEventRepository;

    public function __construct(SchoolTripEventRepositoryInterface $schoolTripEventRepository)
    {
        $this->schoolTripEventRepository = $schoolTripEventRepository;
    }

    public function ready($_, array $args)
    {
        return $this->schoolTripEventRepository->ready($args);
    }

    public function start($_, array $args)
    {
        return $this->schoolTripEventRepository->start($args);
    }

    public function atStation($_, array $args)
    {
        return $this->schoolTripEventRepository->atStation($args);
    }

    public function changePickupStatus($_, array $args)
    {
        return $this->schoolTripEventRepository->changePickupStatus($args);
    }

    public function changeAttendenceStatus($_, array $args)
    {
        return $this->schoolTripEventRepository->changeAttendenceStatus($args);
    }

    public function pickStudents($_, array $args)
    {
        return $this->schoolTripEventRepository->pickStudents($args);
    }

    public function dropStudents($_, array $args)
    {
        return $this->schoolTripEventRepository->dropStudents($args);
    }

    public function updateDriverLocation($_, array $args)
    {
        return $this->schoolTripEventRepository->updateDriverLocation($args);
    }

    public function end($_, array $args)
    {
        return $this->schoolTripEventRepository->end($args);
    }

    public function destroy($_, array $args)
    {
        return $this->schoolTripEventRepository->destroy($args);
    }
}
