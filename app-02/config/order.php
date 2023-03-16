<?php

return [
    'autocomplete' => [
        'ttl'       => env('ORDER_AUTOCOMPLETE_TTL', 10080),
        'curri_ttl' => env('ORDER_AUTOCOMPLETE_CURRI_TTL', 2880),
    ],
];
