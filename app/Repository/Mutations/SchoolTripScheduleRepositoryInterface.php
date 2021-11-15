<?php

namespace App\Repository\Mutations;

interface SchoolTripScheduleRepositoryInterface
{
    public function reschedule(array $args);
}