<?php

namespace App\Notifications;

use App\Constants\Notifications;
use Config;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use NotificationChannels\Fcm\Resources\Notification;

trait SendsPushNotification
{
    private string $androidAnalyticsLabel = 'analytics_android';
    private string $iOSAnalyticsLabel     = 'analytics_ios';
    private string $defaultColor          = '#0A0A0A';

    public function apnsConfig(): ApnsConfig
    {
        return ApnsConfig::create()->setFcmOptions(ApnsFcmOptions::create()
            ->setAnalyticsLabel($this->iOSAnalyticsLabel));
    }

    public function androidConfig(): AndroidConfig
    {
        return AndroidConfig::create()->setFcmOptions(AndroidFcmOptions::create()
            ->setAnalyticsLabel($this->androidAnalyticsLabel))->setNotification(AndroidNotification::create()
            ->setColor($this->defaultColor));
    }

    public function message(Notification $notification): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification($notification)
            ->setAndroid($this->androidConfig())
            ->setApns($this->apnsConfig());
    }

    public function via(): array
    {
        return Config::get('notifications.push.enabled') ? [Notifications::VIA_FCM] : [];
    }

    public function viaQueues(): array
    {
        return [
            Notifications::VIA_FCM => Notifications::QUEUE_FCM,
        ];
    }
}
