<?php

namespace Tests\Unit\Models\SupportCall;

use App\Models\Brand;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupportCall $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupportCall::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $supportCall = SupportCall::factory()->oem()->create();
        $related = $supportCall->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_missing_oem_brand()
    {
        $supportCall = SupportCall::factory()->missingOemBrand()->create();
        $related = $supportCall->missingOemBrand()->first();

        $this->assertInstanceOf(Brand::class, $related);
    }
}
