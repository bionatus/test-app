<?php

return [
    'allowed_countries' => explode(',', env('COMMUNICATIONS_ALLOWED_COUNTRIES', 'US,CA,MX,AU')),

    'phone'        => [
        'verification' => [
            'ttl' => env('PHONE_VERIFICATION_TTL', 5),
        ],
    ],
    'log_requests' => !!(env('LOG_CALL_REQUESTS', false)),
    'calls'        => [
        'max_duration'          => env('CALL_DURATION', 3600 * 4),
        'max_user_waiting_time' => env('CALL_MAX_USER_WAITING_TIME', 120),
        'agent_ringing_time'    => env('CALL_AGENT_RINGING_TIME', 10),
    ],

    'tickets' => [
        'notifications' => [
            'open' => 24 * 60 * 60,
        ],
    ],

    'sms' => [
        'code' => [
            'retry_after' => explode(',', env('SMS_CODE_RETRY_AFTER', '30,60,90')),
            'reset_after' => env('SMS_CODE_RESET_AFTER', 7200),
        ],
    ],
];
