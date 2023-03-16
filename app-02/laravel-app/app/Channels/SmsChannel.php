<?php

namespace App\Channels;

use App\Models\Phone;
use App\Notifications\Phone\SmsRequestedNotification;
use App\Services\Twilio\RestClient;
use Config;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;

class SmsChannel
{
    private RestClient $client;

    public function __construct(RestClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function send(Phone $notifiable, SmsRequestedNotification $notification)
    {
        $phoneNumber = $notifiable->routeNotificationFor('sms', $notification);
        if (!$phoneNumber) {
            return [];
        }

        $message  = $notification->toSms();
        $response = $this->client->messages->create('+' . $phoneNumber, [
            "messagingServiceSid" => Config::get('twilio.sms_services.auth'),
            "body"                => $message,
        ]);

        return [$response];
    }
}
