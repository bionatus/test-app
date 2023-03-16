<?php

namespace Tests\Unit\Models\BrandSupplier;

use App\Models\Brand;
use App\Models\BrandSupplier;
use App\Models\Supplier;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property BrandSupplier $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = BrandSupplier::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_brand()
    {
        $related = $this->instance->brand()->first();

        $this->assertInstanceOf(Brand::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }
}
