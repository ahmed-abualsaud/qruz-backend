<?php

namespace App\Repository\Mutations;

interface SchoolTripStationRepositoryInterface
{
    public function assignStudent(array $args);
    public function acceptStation(array $args);
    public function destroy(array $args);
}