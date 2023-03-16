<?php

namespace App\Constants;

class GeocoderAccuracyValues
{
    const ROOFTOP            = 'ROOFTOP';
    const RANGE_INTERPOLATED = 'RANGE_INTERPOLATED';
    const GEOMETRIC_CENTER   = 'GEOMETRIC_CENTER';
    const APPROXIMATE        = 'APPROXIMATE';
    const INVALID_VALUES     = [
        self::GEOMETRIC_CENTER,
        self::APPROXIMATE,
    ];
}
