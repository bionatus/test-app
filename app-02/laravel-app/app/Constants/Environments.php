<?php

namespace App\Constants;

class Environments
{
    const LOCAL                         = 'local';
    const DEVELOPMENT                   = 'development';
    const QA                            = 'qa';
    const QA2                           = 'qa2';
    const DEMO                          = 'demo';
    const STAGING                       = 'staging';
    const UAT                           = 'uat';
    const PRODUCTION                    = 'production';
    const ALL_BUT_TESTING               = [
        self::LOCAL,
        self::DEVELOPMENT,
        self::QA,
        self::QA2,
        self::DEMO,
        self::STAGING,
        self::UAT,
        self::PRODUCTION,
    ];
    const ALL_BUT_PRODUCTION_OR_TESTING = [
        self::LOCAL,
        self::DEVELOPMENT,
        self::QA,
        self::QA2,
        self::DEMO,
        self::STAGING,
        self::UAT,
    ];
    const ONLY_LOCAL_AND_DEVELOPMENT    = [
        self::LOCAL,
        self::DEVELOPMENT,
    ];
}
