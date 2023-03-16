<?php

namespace Tests\Unit\Models\SupplierInvitation;

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplierInvitation $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplierInvitation::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
