<?php

return [
    'app_name' => env('LIVE_APP_NAME', 'Bluon Live'),
    'url'      => env('LIVE_URL', 'http://localhost/'),
    'routes'   => [
        'password_reset' => env('LIVE_ROUTES_PASSWORD_RESET', '#/password-reset/{token}'),
        'inbound'        => env('LIVE_ROUTES_INBOUND', '#/inbound'),
        'outbound'       => env('LIVE_ROUTES_OUTBOUND', '#/outbound'),
    ],
    'staff'    => [
        'default_password' => env('LIVE_STAFF_DEFAULT_PASSWORD', null),
        'stub_seed_count'  => env('LIVE_STAFF_STUB_SEED_COUNT', 300),
    ],
    'order'    => [
        'summary' => env('LIVE_ORDER_SUMMARY', '#/order-summary?order={order}'),
    ],
    'firebase' => [
        'database_node'                    => env('LIVE_FIREBASE_DATABASE_NODE', 'BL-notification-counters/'),
        'order_delivery_node'              => env('LIVE_FIREBASE_ORDER_DELIVERY_NODE', 'BL-order-deliveries/'),
        'supplier_total_order_node'        => env('LIVE_FIREBASE_SUPPLIER_TOTAL_ORDER_NODE',
            'BL-supplier-total-order/'),
        'supplier_notification_sound_node' => env('LIVE_FIREBASE_SUPPLIER_NOTIFICATION_SOUND_NODE',
            'BL-notification-sound/'),
    ],
    'account'  => [
        'customers'     => env('LIVE_ACCOUNT_CUSTOMERS', '#/account'),
        'notifications' => env('LIVE_ACCOUNT_NOTIFICATIONS', '#/account'),
    ],
];
