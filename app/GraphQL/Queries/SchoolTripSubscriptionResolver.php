<?php

namespace App\GraphQL\Queries;

use App\Repository\Queries\SchoolTripSubscriptionRepositoryInterface;

class SchoolTripSubscriptionResolver
{

    private $schoolTripSubscriptionRepository;
  
    public function __construct(SchoolTripSubscriptionRepositoryInterface $schoolTripSubscriptionRepository)
    {
        $this->schoolTripSubscriptionRepository = $schoolTripSubscriptionRepository;
    }

    public function schoolTripSubscribedStudents($_, array $args)
    {
        return $this->schoolTripSubscriptionRepository->schoolTripSubscribedStudents($args);
    }

    public function schoolTripStationStudents($_, array $args)
    {
        return $this->schoolTripSubscriptionRepository->schoolTripStationStudents($args);
    }

    public function schoolTripSubscribers($_, array $args)
    {
        return $this->schoolTripSubscriptionRepository->schoolTripSubscribers($args);
    }

    public function schoolTripStudentsStatus($_, array $args)
    {
        return $this->schoolTripSubscriptionRepository->schoolTripStudentsStatus($args);
    }

    public function schoolTripStudentStatus($_, array $args)
    {
        return $this->schoolTripSubscriptionRepository->schoolTripStudentStatus($args);
    }

}
