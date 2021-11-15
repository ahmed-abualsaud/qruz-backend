<?php

namespace App\GraphQL\Mutations;

use App\Repository\Eloquent\Mutations\SchoolTripRatingRepository;

class SchoolTripRatingResolver
{
    private $schoolTripRatingRepository;

    public function __construct(SchoolTripRatingRepository $schoolTripRatingRepository)
    {
        $this->schoolTripRatingRepository = $schoolTripRatingRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function update($_, array $args)
    {
        return $this->schoolTripRatingRepository->update($args);
    }
}
