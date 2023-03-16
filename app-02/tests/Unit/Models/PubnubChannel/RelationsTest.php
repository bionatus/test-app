<?php

namespace Tests\Unit\Models\PubnubChannel;

use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PubnubChannel $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $supplier       = Supplier::factory()->createQuietly();
        $this->instance = PubnubChannel::factory()->usingSupplier($supplier)->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }
}
