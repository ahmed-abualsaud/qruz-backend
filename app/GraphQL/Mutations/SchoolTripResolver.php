<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolTripRepositoryInterface;

class SchoolTripResolver
{
    private $schoolTripRepository;

    public function __construct(SchoolTripRepositoryInterface $schoolTripRepository)
    {
        $this->schoolTripRepository = $schoolTripRepository;
    }
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function create($_, array $args)
    {
        return $this->schoolTripRepository->create($args);
    }

    public function update($_, array $args)
    {
        return $this->schoolTripRepository->update($args);
    }

    public function updateRoute($_, array $args)
    {
        return $this->schoolTripRepository->updateRoute($args);
    }

    public function copy($_, array $args)
    {
        return $this->schoolTripRepository->copy($args);
    }

    public function createSubscription($_, array $args)
    {
        return $this->schoolTripRepository->createSubscription($args);
    }

    public function confirmSubscription($_, array $args) 
    {
        return $this->schoolTripRepository->confirmSubscription($args);
    }

    public function deleteSubscription($_, array $args)
    {
        return $this->schoolTripRepository->deleteSubscription($args);
    }

    public function verifySubscription($_, array $args)
    {
        return $this->schoolTripRepository->verifySubscription($args);
    }

    
}
