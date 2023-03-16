<?php

namespace Tests\Unit\Types;

use App\Types\SupplierWorkingHour;
use Tests\TestCase;

class SupplierWorkingHourTest extends TestCase
{
    private string              $date;
    private string              $from;
    private string              $to;
    private string              $timezone;
    private SupplierWorkingHour $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date     = '15/01/2023';
        $this->from     = '9:00 am';
        $this->to       = '5:00 pm';
        $this->timezone = 'America/Chicago';

        $this->instance = new SupplierWorkingHour($this->date, $this->from, $this->to, $this->timezone);
    }

    /** @test */
    public function it_returns_date()
    {
        $this->assertEquals($this->date, $this->instance->date());
    }

    /** @test */
    public function it_returns_from()
    {
        $this->assertEquals($this->from, $this->instance->from());
    }

    /** @test */
    public function it_returns_to()
    {
        $this->assertEquals($this->to, $this->instance->to());
    }

    /** $test */
    public function it_returns_timezone()
    {
        $this->assertEquals($this->timezone, $this->instance->timezone());
    }

    /** @test */
    public function it_returns_utc_if_timezone_is_null()
    {
        $supplierWorkingHour = new SupplierWorkingHour(
            $this->date,
            $this->from,
            $this->to,
            null
        );

        $this->assertEquals('UTC', $supplierWorkingHour->timezone());
    }
}
