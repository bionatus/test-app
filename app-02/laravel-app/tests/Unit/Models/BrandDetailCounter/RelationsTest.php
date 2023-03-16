<?php

namespace Tests\Unit\Models\BrandDetailCounter;

use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\Staff;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property BrandDetailCounter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = BrandDetailCounter::factory()->withStaff()->withUser()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_brand()
    {
        $related = $this->instance->brand()->first();

        $this->assertInstanceOf(Brand::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_staff()
    {
        $related = $this->instance->staff()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }
}
