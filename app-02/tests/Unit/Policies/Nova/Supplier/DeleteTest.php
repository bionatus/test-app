<?php

namespace Tests\Unit\Policies\Nova\Supplier;

use App\Models\Supplier;
use App\Policies\Nova\SupplierPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_delete_a_supplier()
    {
        $policy   = new SupplierPolicy();
        $user     = Mockery::mock(User::class);
        $supplier = Mockery::mock(Supplier::class);

        $this->assertFalse($policy->delete($user, $supplier));
    }
}
