<?php

namespace Tests\Unit\Types;

use App\Types\CompanyDataType;
use Tests\TestCase;

class CompanyDataTypeTest extends TestCase
{
    /** @test */
    public function it_returns_an_empty_array_on_invalid_company_type()
    {
        $companyType = 'invalid type';
        $response    = CompanyDataType::getJobTitles($companyType);

        $this->assertEmpty($response);
    }

    /** @test
     *
     * @dataProvider dataProvider
     */
    public function it_returns_an_array_of_job_titles_for_a_company_type(string $companyType, array $expectedResponse)
    {
        $response = CompanyDataType::getJobTitles($companyType);

        $this->assertEquals($expectedResponse, $response);
    }

    public function dataProvider(): array
    {
        return [
            [
                CompanyDataType::TYPE_CONTRACTOR,
                [
                    'Service Technician',
                    'Installer',
                    'Service Manager',
                    'Owner',
                    'Sales',
                    'Engineer',
                    'Other',
                ],
            ],
            [
                CompanyDataType::TYPE_TRADE_SCHOOL,
                [
                    'Student',
                    'Instructor',
                    'Other',
                ],
            ],
            [
                CompanyDataType::TYPE_SUPPLY_HOUSE,
                [
                    'Inside Sales/Counter Sales',
                    'Outside Sales',
                    'Branch Manager',
                    'Executive',
                    'Accounting',
                    'Fulfillment and logistics',
                    'Other',
                ],
            ],
            [
                CompanyDataType::TYPE_OEM,
                [
                    'Engineer',
                    'Sales',
                    'Business Development',
                    'Marketing',
                    'IT/Software Development/E-commerce',
                    'Executive',
                    'Other',
                ],
            ],
            [
                CompanyDataType::TYPE_PROPERTY_MANAGER_OWNER,
                [
                    'In-house technician',
                    'Building Engineer',
                    'Other',
                ],
            ],
        ];
    }
}
