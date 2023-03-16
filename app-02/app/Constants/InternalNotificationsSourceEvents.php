<?php

namespace App\Constants;

use App\Models\Supplier;

class InternalNotificationsSourceEvents
{
    const NEW_MESSAGE = 'new-message';
    const SUPPLIER_SOURCE_EVENTS = [
        self::NEW_MESSAGE,
    ];
    const ALL = [
        Supplier::MORPH_ALIAS => self::SUPPLIER_SOURCE_EVENTS,
    ];
}
