<?php

namespace App\GraphQL\Mutations;

use App\Repository\Mutations\SchoolCommunicationRepositoryInterface;


class SchoolCommunicationResolver
{
    private $schoolCommunicationRepository;

    public function  __construct(SchoolCommunicationRepositoryInterface $schoolCommunicationRepository)
    {
        $this->schoolCommunicationRepository = $schoolCommunicationRepository;
    }

     /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */

    public function sendSchoolTripChatMessage($_, array $args)
    {
        return $this->schoolCommunicationRepository->sendSchoolTripChatMessage($args);
    }
}
