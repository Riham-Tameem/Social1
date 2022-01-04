<?php

namespace App\Repositories;

use App\Http\Controllers\Api\BaseController;
use App\Models\FcmNotification;
use App\Models\FcmToken;
use App\Models\User;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class NotificationEloquent extends BaseController
{

    function sendNotification($sender_id, $receiver_id, $action, $action_id)
    {

        if ($sender_id != $receiver_id) {
            // create notification
            $notification = $this->createNotification($sender_id, $receiver_id, $action, $action_id);

            $tokens = FcmToken::where('user_id', $receiver_id)->where('status', 1)->pluck('fcm_token')->toArray();

            $sender = User::find($sender_id);
            $title = __('app.notification_action.' . $action);
            $message = __('app.notification_message.' . $action, ['user' => $sender->name]);

            if (count($tokens) > 0)
                return $this->fcm($title, $message, $notification, $tokens);
            //  $fcm=$this->fcm($title, $message, $notification, $tokens);
            //   dd($fcm);
        }
    }

    function fcm($title, $body, $data, $tokens, $badge = 1)
    {

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default')->setBadge($badge);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => $data]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();

        $fcm = [
            'numberSuccess' => $downstreamResponse->numberSuccess(),
            'numberFailure' => $downstreamResponse->numberFailure(),
            'numberModification' => $downstreamResponse->numberModification()
        ];


        return $fcm;
    }

    function createNotification($sender_id, $receiver_id, $action, $action_id)
    {
        $notification = FcmNotification::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'action' => $action,
            'action_id' => $action_id
        ]);

        return $notification;
    }
}
