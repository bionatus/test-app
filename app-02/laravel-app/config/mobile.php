<?php

return [
    'firebase' => [
        'database_node'       => env('MOBILE_FIREBASE_DATABASE_NODE', 'BM-notification-counters/'),
        'order_delivery_node' => env('MOBILE_FIREBASE_ORDER_DELIVERY_NODE', 'BM-order-deliveries/'),
        'order_status_node'   => env('MOBILE_FIREBASE_ORDER_STATUS_NODE', 'BM-order-statuses/'),
    ],
];
