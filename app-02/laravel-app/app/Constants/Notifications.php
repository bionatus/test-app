<?php

namespace App\Constants;

use NotificationChannels\Fcm\FcmChannel;

final class Notifications
{
    const VIA_FCM   = FcmChannel::class;
    const QUEUE_FCM = 'fcm-queue';
}
