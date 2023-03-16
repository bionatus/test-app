<?php

return [
    'log_requests' => !!(env('LOG_API_USAGE', true)),
    'tracking_timezone' => env('API_USAGE_TRACKING_TIMEZONE', 'America/Los_Angeles'),
];
