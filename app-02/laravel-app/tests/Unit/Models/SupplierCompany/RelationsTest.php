<?php

namespace Tests\Unit\Models\SupplierCompany;

use App\Models\Supplier;
use App\Models\SupplierCompany;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplierCompany $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplierCompany::factory()->create();
    }

    /** @test */
    public function it_has_suppliers()
    {
        Supplier::factory()->usingSupplierCompany($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->supplier()->get();

        $this->assertCorrectRelation($related, Supplier::class);
    }
}
