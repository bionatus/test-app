<?php

namespace Tests\Unit\Policies\Nova\User;

use App\Models\Supplier;
use App\Models\User as UserModel;
use App\Policies\Nova\UserPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewSupplierTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_view_supplier()
    {
        $policy    = new UserPolicy();
        $user      = Mockery::mock(User::class);
        $userModel = Mockery::mock(UserModel::class);
        $supplier  = Mockery::mock(Supplier::class);

        $this->assertFalse($policy->viewSupplier($user, $userModel, $supplier));
    }
}
