<?php

namespace Tests\Unit\Policies\Nova\Supplier;

use App\Policies\Nova\SupplierPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_supplier()
    {
        $policy = new SupplierPolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->create($user));
    }
}
