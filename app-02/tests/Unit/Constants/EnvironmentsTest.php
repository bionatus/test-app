<?php

namespace Tests\Unit\Constants;

use App\Constants\Environments;
use PHPUnit\Framework\TestCase;

class EnvironmentsTest extends TestCase
{
    /** @test */
    public function its_local_constant_is_a_specific_string()
    {
        $this->assertSame('local', Environments::LOCAL);
    }

    /** @test */
    public function its_development_constant_is_a_specific_string()
    {
        $this->assertSame('development', Environments::DEVELOPMENT);
    }

    /** @test */
    public function its_qa_constant_is_a_specific_string()
    {
        $this->assertSame('qa', Environments::QA);
    }

    /** @test */
    public function its_demo_constant_is_a_specific_string()
    {
        $this->assertSame('demo', Environments::DEMO);
    }

    /** @test */
    public function its_staging_constant_is_a_specific_string()
    {
        $this->assertSame('staging', Environments::STAGING);
    }

    /** @test */
    public function its_uat_constant_is_a_specific_string()
    {
        $this->assertSame('uat', Environments::UAT);
    }

    /** @test */
    public function its_production_constant_is_a_specific_string()
    {
        $this->assertSame('production', Environments::PRODUCTION);
    }
}
