<?php
namespace App\Repository\Queries;

interface SchoolTripRepositoryInterface
{
    public function studentSubscriptions(array $args);
    public function studentTrips(array $args);
    public function studentLiveTrips(array $args);
    public function driverTrips(array $args);
    public function driverLiveTrips(array $args);
    public function studentHistory(array $args);
}