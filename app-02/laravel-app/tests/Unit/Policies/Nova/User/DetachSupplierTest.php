<?php

namespace Tests\Unit\Policies\Nova\User;

use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User as UserModel;
use App\Policies\Nova\UserPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class DetachSupplierTest extends TestCase
{
    /** @test */
    public function it_allows_to_detach_a_supplier()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $userModel = Mockery::mock(UserModel::class);
        $userModel->shouldReceive('getKey')->andReturn(1);

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('visible_by_user')->andReturn(true);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->andReturnNull();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('where')->withAnyArgs()->andReturn($hasMany);
        $hasMany->shouldReceive('first')->withNoArgs()->andReturn($supplierUser);

        $hasManyOrders = Mockery::mock(HasMany::class);
        $hasManyOrders->shouldReceive('scoped')->withAnyArgs()->andReturn($hasManyOrders);
        $hasManyOrders->shouldReceive('count')->withNoArgs()->andReturn(0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('supplierUsers')->withNoArgs()->andReturn($hasMany);
        $supplier->shouldReceive('orders')->withNoArgs()->andReturn($hasManyOrders);

        $this->assertTrue($policy->detachSupplier($user, $userModel, $supplier));
    }

    /** @test */
    public function it_does_not_allow_to_detach_supplier_when_visible_by_user_is_false()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $userModel = Mockery::mock(UserModel::class);
        $userModel->shouldReceive('getKey')->andReturn(1);

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('visible_by_user')->andReturn(false);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->andReturnNull();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('where')->withAnyArgs()->andReturn($hasMany);
        $hasMany->shouldReceive('first')->withNoArgs()->andReturn($supplierUser);

        $hasManyOrders = Mockery::mock(HasMany::class);
        $hasManyOrders->shouldReceive('scoped')->withAnyArgs()->andReturn($hasManyOrders);
        $hasManyOrders->shouldReceive('count')->withNoArgs()->andReturn(0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('supplierUsers')->withNoArgs()->andReturn($hasMany);
        $supplier->shouldReceive('orders')->withNoArgs()->andReturn($hasManyOrders);

        $this->assertFalse($policy->detachSupplier($user, $userModel, $supplier));
    }

    /** @test */
    public function it_does_not_allow_to_detach_supplier_when_customer_tier_has_data()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $userModel = Mockery::mock(UserModel::class);
        $userModel->shouldReceive('getKey')->andReturn(1);

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('visible_by_user')->andReturn(true);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->andReturn('foo');

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('where')->withAnyArgs()->andReturn($hasMany);
        $hasMany->shouldReceive('first')->withNoArgs()->andReturn($supplierUser);

        $hasManyOrders = Mockery::mock(HasMany::class);
        $hasManyOrders->shouldReceive('scoped')->withAnyArgs()->andReturn($hasManyOrders);
        $hasManyOrders->shouldReceive('count')->withNoArgs()->andReturn(0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('supplierUsers')->withNoArgs()->andReturn($hasMany);
        $supplier->shouldReceive('orders')->withNoArgs()->andReturn($hasManyOrders);

        $this->assertFalse($policy->detachSupplier($user, $userModel, $supplier));
    }

    /** @test */
    public function it_does_not_allow_to_detach_supplier_when_has_orders_between_user_and_supplier()
    {
        $policy = new UserPolicy();
        $user   = Mockery::mock(User::class);

        $userModel = Mockery::mock(UserModel::class);
        $userModel->shouldReceive('getKey')->andReturn(1);

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('visible_by_user')->andReturn(true);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->andReturnNull();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('where')->withAnyArgs()->andReturn($hasMany);
        $hasMany->shouldReceive('first')->withNoArgs()->andReturn($supplierUser);

        $hasManyOrders = Mockery::mock(HasMany::class);
        $hasManyOrders->shouldReceive('scoped')->withAnyArgs()->andReturn($hasManyOrders);
        $hasManyOrders->shouldReceive('count')->withNoArgs()->andReturn(1);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('supplierUsers')->withNoArgs()->andReturn($hasMany);
        $supplier->shouldReceive('orders')->withNoArgs()->andReturn($hasManyOrders);

        $this->assertFalse($policy->detachSupplier($user, $userModel, $supplier));
    }
}
