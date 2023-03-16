<?php
return [
    'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
    'api_key'      => env('HUBSPOT_API_KEY'),
    'form_url'     => env('HUBSPOT_FORM_URL', 'http://localhost?email='),
];
