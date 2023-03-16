<?php

namespace Tests\Unit\Policies\Nova\Supplier;

use App\Models\Supplier;
use App\Policies\Nova\SupplierPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view_a_supplier()
    {
        $policy   = new SupplierPolicy();
        $user     = Mockery::mock(User::class);
        $supplier = Mockery::mock(Supplier::class);

        $this->assertTrue($policy->view($user, $supplier));
    }
}
