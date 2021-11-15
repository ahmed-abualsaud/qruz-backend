<?php

namespace App\Repository\Mutations;

interface SchoolTripRequestRepositoryInterface
{
    public function createTrip(array $args);
    public function addToTrip(array $args);
}