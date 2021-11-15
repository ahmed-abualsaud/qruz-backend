<?php

namespace App\Repository\Queries;

interface SchoolCommunicationRepositoryInterface
{
    public function schoolTripChatMessages(array $args);
    public function schoolTripPrivateChatUsers(array $args);
}