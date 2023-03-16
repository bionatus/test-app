<?php

namespace App\Constants;

class Timezones
{
    const AMERICA_ADAK        = 'America/Adak';
    const AMERICA_ANCHORAGE   = 'America/Anchorage';
    const AMERICA_CHICAGO     = 'America/Chicago';
    const AMERICA_DENVER      = 'America/Denver';
    const AMERICA_LOS_ANGELES = 'America/Los_Angeles';
    const AMERICA_NEW_YORK    = 'America/New_York';
    const AMERICA_PHOENIX     = 'America/Phoenix';
    const PACIFIC_GUAM        = 'Pacific/Guam';
    const ALLOWED_TIMEZONES   = [
        self::AMERICA_ADAK,
        self::AMERICA_ANCHORAGE,
        self::AMERICA_CHICAGO,
        self::AMERICA_DENVER,
        self::AMERICA_LOS_ANGELES,
        self::AMERICA_NEW_YORK,
        self::AMERICA_PHOENIX,
    ];
}
