<?php

return [
    'export-order-invoices'                => [
        'day'      => env('EXPORT_ORDER_INVOICES_DAY', 8),
        'hour'     => env('EXPORT_ORDER_INVOICES_HOUR', '07:00'),
        'timezone' => env('EXPORT_ORDER_INVOICES_TIMEZONE', 'UTC'),
    ],
    'send-order-pending-approval-reminder' => [
        'hour' => env('SEND_ORDER_PENDING_APPROVAL_REMINDER_HOUR', '07:00'),
    ],
    'xoxo-update-vouchers'                 => [
        'day' => env('XOXO_UPDATE_VOUCHERS', '01:00'),
    ],
];
