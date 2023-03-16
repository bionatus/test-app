<?php

namespace Tests\Unit\Models\SupplierListView;

use App\Models\SupplierListView;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SupplierListView $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SupplierListView::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
