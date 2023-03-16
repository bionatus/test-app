<?php

namespace App\Constants;

class SupplierChangeRequestReasons
{
    const REASON_NOT_REAL        = 'Not a real store!';
    const REASON_INCORRECT_WRONG = 'Incorrect address / Wrong store';
    const REASON_OTHER           = 'Other';
    const ALL                    = [
        self::REASON_NOT_REAL,
        self::REASON_INCORRECT_WRONG,
        self::REASON_OTHER,
    ];
}
