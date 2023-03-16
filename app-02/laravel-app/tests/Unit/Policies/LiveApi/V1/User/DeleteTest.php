<?php

namespace Tests\Unit\Policies\LiveApi\V1\User;

use App\Models\Staff;
use App\Models\SupplierUser;
use App\Models\User;
use App\Policies\LiveApi\V1\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_a_supplier_to_delete_a_non_related_user()
    {
        $staff = Staff::factory()->createQuietly();
        $user  = User::factory()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->delete($staff, $user));
    }

    /** @test */
    public function it_disallows_a_supplier_to_delete_an_unconfirmed_related_user()
    {
        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->delete($staff, $supplierUser->user));
    }

    /** @test */
    public function it_disallows_a_supplier_to_delete_a_removed_related_user()
    {
        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->removed()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->delete($staff, $supplierUser->user));
    }

    /** @test */
    public function it_allows_a_supplier_to_delete_a_confirmed_related_user()
    {
        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create();

        $policy = new UserPolicy();

        $this->assertTrue($policy->delete($staff, $supplierUser->user));
    }
}
