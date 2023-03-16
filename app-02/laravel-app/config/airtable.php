<?php

return [

    'url'                 => 'https://api.airtable.com',
    'version'             => 'v0',
    'token'               => env('AIRTABLE_TOKEN', null),
    'table'               => env('AIRTABLE_TABLE', null),
    'suppliers_table'     => env('AIRTABLE_SUPPLIERS_TABLE'),
    'common_items_table'  => env('AIRTABLE_COMMON_ITEMS_TABLE'),
    'endpoints'           => [
        'products'     => env('AIRTABLE_PRODUCTS_ENDPOINT', null),
        'conversions'  => env('AIRTABLE_CONVERSIONS_ENDPOINT', null),
        'warnings'     => env('AIRTABLE_WARNINGS_ENDPOINT', null),
        'suppliers'    => env('AIRTABLE_SUPPLIERS_ENDPOINT', null),
        'common_items' => env('AIRTABLE_COMMON_ITEMS_ENDPOINT', null),
    ],
    'results_per_request' => 100,
    'requests_limit'      => 5, // 5 subsequent requests max
    'requests_time_limit' => 1, // seconds until requests limit is reset
    'daily_sync_at'       => env('AIRTABLE_DAILY_SYNC_AT', '00:00'),
];
