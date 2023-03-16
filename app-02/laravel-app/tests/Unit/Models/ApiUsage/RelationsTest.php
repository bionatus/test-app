<?php

namespace Tests\Unit\Models\ApiUsage;

use App\Models\ApiUsage;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    /** @test */
    public function it_has_a_user()
    {
        $userUsage = ApiUsage::factory()->withUser()->create();
        $user      = $userUsage->user()->first();
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function it_has_a_supplier()
    {
        $supplierUsage = ApiUsage::factory()->withSupplier()->createQuietly();
        $user          = $supplierUsage->supplier()->first();
        $this->assertInstanceOf(Supplier::class, $user);
    }
}
