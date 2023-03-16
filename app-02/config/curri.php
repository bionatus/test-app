<?php

return [
    'api_endpoint'        => env('CURRI_API_ENDPOINT', 'https://api.curri.com/graphql'),
    'api_key'             => env('CURRI_API_KEY'),
    'user_id'             => env('CURRI_USER_ID'),
    'prefix_tracking_url' => env('CURRI_PREFIX_TRACKING_URL', 'https://app.curri.com/track/'),
];
