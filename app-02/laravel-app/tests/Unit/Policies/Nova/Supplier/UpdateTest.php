<?php

namespace Tests\Unit\Policies\Nova\Supplier;

use App\Models\Supplier;
use App\Policies\Nova\SupplierPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_a_supplier()
    {
        $policy   = new SupplierPolicy();
        $user     = Mockery::mock(User::class);
        $supplier = Mockery::mock(Supplier::class);

        $this->assertTrue($policy->update($user, $supplier));
    }
}
