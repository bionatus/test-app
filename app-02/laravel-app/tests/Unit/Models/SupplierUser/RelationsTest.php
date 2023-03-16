<?php

namespace Tests\Unit\Models\SupplierUser;

use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplierUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplierUser::factory()->createQuietly();
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
