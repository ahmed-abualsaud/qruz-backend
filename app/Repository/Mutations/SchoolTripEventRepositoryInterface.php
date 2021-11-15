<?php

namespace App\Repository\Mutations;

interface SchoolTripEventRepositoryInterface
{
    public function ready(array $args);
    public function start(array $args);
    public function atStation(array $args);
    public function changePickupStatus(array $args);
    public function changeAttendenceStatus(array $args);
    public function pickStudents(array $args);
    public function dropStudents(array $args);
    public function updateDriverLocation(array $args);
    public function end(array $args);
    public function destroy(array $args);
}