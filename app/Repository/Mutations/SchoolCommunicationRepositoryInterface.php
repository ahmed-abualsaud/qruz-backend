<?php

namespace App\Repository\Mutations;

interface SchoolCommunicationRepositoryInterface
{
    public function sendSchoolTripChatMessage(array $args);
}