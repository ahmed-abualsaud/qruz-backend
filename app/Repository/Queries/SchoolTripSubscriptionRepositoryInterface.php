<?php

namespace App\Repository\Queries;

use Illuminate\Support\Collection;

interface SchoolTripSubscriptionRepositoryInterface
{
    public function schoolTripSubscribedStudents(array $args): Collection;
    public function schoolTripStationStudents(array $args): Collection;
    public function schoolTripSubscribers(array $args): Collection;
    public function schoolTripStudentsStatus(array $args): Collection;
    public function schoolTripStudentStatus(array $args);
}