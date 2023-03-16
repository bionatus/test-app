<?php

return [
    'publish_key'   => env('PUBNUB_PUBLISH_KEY'),
    'secret_key'    => env('PUBNUB_SECRET_KEY'),
    'subscribe_key' => env('PUBNUB_SUBSCRIBE_KEY'),
    'ttl'           => env('PUBNUB_TTL', 42300),
    'uuid'          => env('PUBNUB_UUID'),
];
