<?php

namespace Tests\Unit\Models\SupplierHour;

use App\Models\Supplier;
use App\Models\SupplierHour;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplierHour $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplierHour::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }
}
