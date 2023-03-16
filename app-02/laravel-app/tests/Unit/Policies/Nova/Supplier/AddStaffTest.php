<?php

namespace Tests\Unit\Policies\Nova\Supplier;

use App\Models\Supplier;
use App\Policies\Nova\SupplierPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class AddStaffTest extends TestCase
{
    /** @test */
    public function it_does_not_allows_to_add_a_staff_if_its_staff_list_is_equal_or_more_than_10()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn(10);
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('staff')->withNoArgs()->once()->andReturn($hasMany);
        $user = Mockery::mock(User::class);

        $policy = new SupplierPolicy();

        $this->assertFalse($policy->addStaff($user, $supplier));
    }

    /** @test */
    public function it_allows_to_add_a_staff_if_its_staff_list_is_less_than_10()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn(9);
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('staff')->withNoArgs()->once()->andReturn($hasMany);
        $user = Mockery::mock(User::class);

        $policy = new SupplierPolicy();

        $this->assertTrue($policy->addStaff($user, $supplier));
    }
}
