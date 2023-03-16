<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\Preferred;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferredTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_preferred()
    {
        $user = User::factory()->create();

        Supplier::factory()->count(2)->createQuietly()->fresh();
        SupplierUser::factory()->count(3)->createQuietly();
        SupplierUser::factory()->createQuietly(['preferred' => true]);
        SupplierUser::factory()->usingUser($user)->count(3)->createQuietly();
        $preferredExpected = SupplierUser::factory()->usingUser($user)->createQuietly(['preferred' => true]);

        $ordered = Supplier::scoped(new Preferred($user))->get();

        $this->assertSame($preferredExpected->supplier->getKey(), $ordered->first()->getKey());
    }
}
