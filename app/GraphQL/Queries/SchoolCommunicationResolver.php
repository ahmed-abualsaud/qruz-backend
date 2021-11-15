<?php

namespace App\GraphQL\Queries;

use App\Repository\Queries\SchoolCommunicationRepositoryInterface;

class SchoolCommunicationResolver
{
    private $schoolCommunicationRepository;
  
    public function __construct(SchoolCommunicationRepositoryInterface $schoolCommunicationRepository)
    {
        $this->schoolCommunicationRepository =  $schoolCommunicationRepository;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function schoolTripChatMessages($_, array $args)
    {
        return $this->schoolCommunicationRepository->schoolTripChatMessages($args);
    }

    public function schoolTripPrivateChatUsers($_, array $args)
    {
        return $this->schoolCommunicationRepository->schoolTripPrivateChatUsers($args);
    }

    public function studentPrivateChatMessages($_, array $args)
    {
        return $this->schoolCommunicationRepository->studentPrivateChatMessages($args);
    }
}
