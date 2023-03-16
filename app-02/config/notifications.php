<?php

return [
    'push' => [
        'enabled'         => env('NOTIFICATIONS_PUSH_ENABLED', true),
        'min_app_version' => env('NOTIFICATIONS_PUSH_MIN_APP_VERSION', '5.2.0'),
    ],
    'sms'  => [
        'enabled' => env('NOTIFICATIONS_SMS_ENABLED', true),
    ],
];
