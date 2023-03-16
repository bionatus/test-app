<?php

namespace App\Constants;

use App\Listeners\User\SendInitialPubnubMessage;

class PubnubMessageTypes
{
    const CURRI_DELIVERY_ON_ROUTE          = [
        'text'         => 'Your driver is on route! Click below to track their progress.',
        'tracking_url' => null,
        'type'         => 'curri',
    ];
    const INITIAL_MESSAGE                  = [
        'text' => 'Hey **:' . SendInitialPubnubMessage::PLACEHOLDER_USER_NAME . '**! You\'re now connected with **:' . SendInitialPubnubMessage::PLACEHOLDER_SUPPLIER_NAME . '** at **:' . SendInitialPubnubMessage::PLACEHOLDER_SUPPLIER_ADDRESS . '** in **:' . SendInitialPubnubMessage::PLACEHOLDER_SUPPLIER_CITY . '**. You can now send quote requests to this location and message here directly!',
        'type' => 'auto_message',
    ];
    const NEW_ORDER                        = [
        'text' => 'New Quote Request',
        'type' => 'new_order',
    ];
    const NEW_ORDER_IN_WORKING_HOURS       = [
        'text' => 'Your Quote Request has been successfully sent.',
        'type' => 'text',
    ];
    const NEW_ORDER_NOT_IN_WORKING_HOURS   = [
        'text' => 'Your Quote Request has been successfully sent. Currently this branch is closed, your supplier will see your request on their next business day.',
        'type' => 'text',
    ];
    const ORDER_APPROVED                   = [
        'bid_number' => null,
        'po_number'  => null,
        'text'       => 'Quote Approved',
        'type'       => 'order_approved',
    ];
    const ORDER_APPROVED_AUTOMATIC_MESSAGE = [
        'text' => 'Your approved quote has been received. Chat is available for pickup questions.',
        'type' => 'auto_message',
    ];
    const ORDER_ASSIGNED                   = [
        'text' => ':staff is working on your quote. Stay tuned!',
        'type' => 'auto_message',
    ];
    const ORDER_CANCELED                   = [
        'bid_number' => null,
        'text'       => 'Quote Cancelled',
        'type'       => 'order_canceled',
    ];
    const ORDER_CANCELED_BY_SUPPLIER       = [
        'text' => 'Order Cancelled',
        'type' => 'order_declined',
    ];
    const ORDER_DECLINED                   = [
        'text' => 'Order Request Declined',
        'type' => 'order_declined',
    ];
    const ORDER_SENT_FOR_APPROVAL          = [
        'text' => 'Here’s the quote. You can view and approve or share the link if you need approval.',
        'type' => 'text',
    ];
    const ORDER_SENT_FOR_APPROVAL_LINK     = [
        'orderId'    => null,
        'order_id'   => null,
        'shareLink'  => null,
        'share_link' => null,
        'text'       => 'Tap the link to approve or share your parts quote.',
        'type'       => 'instant_quote',
    ];
    const TEXT                             = [
        'text' => null,
        'type' => 'text',
    ];
}
