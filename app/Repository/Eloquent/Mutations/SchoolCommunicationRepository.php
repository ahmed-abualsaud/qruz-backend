<?php

namespace App\Repository\Eloquent\Mutations;

use App\Jobs\SendOtp;
use App\SchoolTripChat;
use App\Mail\DefaultMail;
use App\Events\MessageSent;
use Illuminate\Support\Arr;
use App\Jobs\SendPushNotification;
use App\Traits\HandleDeviceTokens;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Mail;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Mutations\SchoolCommunicationRepositoryInterface;

class SchoolCommunicationRepository extends BaseRepository implements SchoolCommunicationRepositoryInterface
{
    use HandleDeviceTokens;

    public function __construct(SchoolTripChat $model)
    {
        parent::__construct($model);
    }

    public function sendSchoolTripChatMessage(array $args)
    {
        $message = $this->createMessage($args);
        $sender = $this->getSender($args['sender_type']);

        if(array_key_exists('recipient_id', $args) && $args['recipient_id']) {
            $this->notifyRecipient($args, $message['message'], $sender);
            $args['private'] = true;
        } else {
            $this->notifyGroup($args, $message['message'], $sender);
            $args['private'] = false;
        }

        $this->broadcastMessage($message, $sender, $args);

        return $message;
    }

    protected function createMessage($args)
    {
        try {
            $input = Arr::except($args, ['directive', 'driver_id', 'trip_id', 'trip_name']);
            if(array_key_exists('recipient_id', $args) && $args['recipient_id']) 
                $input['is_private'] = true;
            $msg = $this->model->create($input);
            $msg->time = date('h:i a');
            return $msg;
        } catch (\Exception $e) {
            throw new CustomException(__('lang.save_message_failed'));
        }
    }

    protected function getSender($sender_type)
    {
        try {
            $guard = strtolower(str_replace('App\\', '', $sender_type));
            return auth($guard)->user();
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function notifyRecipient($args, $msg, $sender)
    {
        try {
            switch($args['sender_type']) {
                case 'App\\User':
                    $token = $this->driverToken($args['recipient_id']);
                break;
                default:
                    $token = $this->userToken($args['recipient_id']);
            }

            SendPushNotification::dispatch(
                $token, 
                $sender->name.': '.$msg, 
                $args['trip_name'],
                [
                    'view' => 'BusinessTripDirectMessage', 
                    'id' => $args['trip_id'], 
                    'sender_id' => $args['sender_id']
                ]
            );
        } catch(\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function notifyGroup($args, $msg, $sender)
    {
        try {
            switch ($args['sender_type']) {
                case 'App\\User':
                    $tokens = $this->tripUsersTokenWithout($args['trip_id'], $sender->id);
                    array_push($tokens, $this->driverToken($args['driver_id']));
                    break;
                case 'App\\Driver':
                    $tokens = $this->tripUsersToken($args['trip_id']);
                    break;
                default:
                    $tokens = $this->tripUsersToken($args['trip_id']);
                    array_push($tokens, $this->driverToken($args['driver_id']));
                    break;
            }
    
            SendPushNotification::dispatch(
                $tokens, 
                $sender->name.': '.$msg, 
                $args['trip_name'],
                [
                    'view' => 'BusinessTripGroupChat', 
                    'id' => $args['trip_id']
                ]
            );
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function broadcastMessage($msg, $sender, $args)
    {
        try {
            $res = [ 
                'id' => $msg['id'],
                'message' => $msg['message'],
                'created_at' => date('Y-m-d H:i:s'),
                'time' => date('h:i a'),
                'sender' => [
                    'id' => $sender->id,
                    'name' => $sender->name,
                    '__typename' => 'Sender'
                ],
                'sender_type' => $msg['sender_type'],
                '__typename' => 'BusinessTripChat'
            ];
    
            broadcast(new MessageSent($this->getChannelName($args), $res))->toOthers();
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function getChannelName($args)
    {
        if ($args['private']) {
            $user_id = $args['sender_type'] == 'App\\User' 
                ? $args['sender_id'] 
                : $args['recipient_id'];

            return 'App.SchoolTripPrivateChat.'.$args['log_id'].'.'.$user_id;
        }

        return 'App.SchoolTrip.'.$args['log_id'];
    }
}
