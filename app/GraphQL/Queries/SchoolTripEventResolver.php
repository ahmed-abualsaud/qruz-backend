<?php

namespace App\GraphQL\Queries;

use App\Repository\Eloquent\Queries\SchoolTripEventRepository;

class SchoolTripEventResolver
{

    private $schoolTripEventRepository;
  
    public function __construct(SchoolTripEventRepository $schoolTripEventRepository)
    {
        $this->schoolTripEventRepository = $schoolTripEventRepository;
    }

    public function index($_, array $args)
    {
        return $this->schoolTripEventRepository->index($args);
    }
}
