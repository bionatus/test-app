<?php

namespace App\NotificationChannels;

use NotificationChannels\Twilio\Exceptions\CouldNotSendNotification;
use NotificationChannels\Twilio\TwilioChannel;

class TwilioByProkeepPhoneChannel extends TwilioChannel
{
    protected function getTo($notifiable, $notification = null)
    {
        if ($notifiable->routeNotificationFor('TwilioByProkeepPhone', $notification)) {
            return $notifiable->routeNotificationFor('TwilioByProkeepPhone', $notification);
        }

        throw CouldNotSendNotification::invalidReceiver();
    }
}
