<?php

return [
    'account_sid'                 => env('TWILIO_ACCOUNT_SID'),
    'auth_token'                  => env('TWILIO_AUTH_TOKEN'),
    'api_key'                     => env('TWILIO_API_KEY'),
    'api_secret'                  => env('TWILIO_API_KEY_SECRET'),
    'android_push_credential_sid' => env('TWILIO_ANDROID_PUSH_CREDENTIAL_SID'),
    'ios_push_credential_sid'     => env('TWILIO_IOS_PUSH_CREDENTIAL_SID'),
    'app_sid'                     => env('TWILIO_APP_SID'),
    'app_sids'                    => [
        'auth' => env('TWILIO_APP_AUTH_SID'),
    ],
    'sms_services'                => [
        'auth' => env('TWILIO_SMS_SERVICE_AUTH'),
    ],
    'numbers' => [
        'auth' => env('TWILIO_NUMBER_AUTH')
    ]
];
