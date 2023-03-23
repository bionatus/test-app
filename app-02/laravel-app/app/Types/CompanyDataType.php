<?php

namespace App\Types;

class CompanyDataType
{
    const TYPE_CONTRACTOR             = 'Contractor';
    const TYPE_SUPPLY_HOUSE           = 'Supply House';
    const TYPE_TRADE_SCHOOL           = 'Trade School';
    const TYPE_OEM                    = 'OEM';
    const TYPE_PROPERTY_MANAGER_OWNER = 'Property Manager / Property Owner';
    const ALL_COMPANY_TYPES           = [
        self::TYPE_CONTRACTOR,
        self::TYPE_SUPPLY_HOUSE,
        self::TYPE_TRADE_SCHOOL,
        self::TYPE_OEM,
        self::TYPE_PROPERTY_MANAGER_OWNER,
    ];
    const EQUIPMENT_TYPE_RESIDENTIAL            = 'Residential Only';
    const EQUIPMENT_TYPE_RESIDENTIAL_COMMERCIAL = 'Residential / Light Commercial';
    const EQUIPMENT_TYPE_COMMERCIAL             = 'Commercial';
    const EQUIPMENT_TYPE_INDUSTRIAL             = 'Industrial';
    const ALL_EQUIPMENT_TYPES                   = [
        self::EQUIPMENT_TYPE_RESIDENTIAL,
        self::EQUIPMENT_TYPE_RESIDENTIAL_COMMERCIAL,
        self::EQUIPMENT_TYPE_COMMERCIAL,
        self::EQUIPMENT_TYPE_INDUSTRIAL,
    ];
    protected const JOB_TITLES = [
        self::TYPE_CONTRACTOR             => [
            'Service Technician',
            'Installer',
            'Service Manager',
            'Owner',
            'Sales',
            'Engineer',
            'Other',
        ],
        self::TYPE_TRADE_SCHOOL           => [
            'Student',
            'Instructor',
            'Other',
        ],
        self::TYPE_SUPPLY_HOUSE           => [
            'Inside Sales/Counter Sales',
            'Outside Sales',
            'Branch Manager',
            'Executive',
            'Accounting',
            'Fulfillment and logistics',
            'Other',
        ],
        self::TYPE_OEM                    => [
            'Engineer',
            'Sales',
            'Business Development',
            'Marketing',
            'IT/Software Development/E-commerce',
            'Executive',
            'Other',
        ],
        self::TYPE_PROPERTY_MANAGER_OWNER => [
            'In-house technician',
            'Building Engineer',
            'Other',
        ],
    ];

    public static function getJobTitles(?string $companyType = null): array
    {
        $response = [];
        if (!is_null($companyType) && key_exists($companyType, self::JOB_TITLES)) {
            return self::JOB_TITLES[$companyType];
        }

        return $response;
    }
}
