<?php

namespace Tests\Unit\Policies\LiveApi\V1\User;

use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Policies\LiveApi\V1\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_a_supplier_to_remove_a_non_related_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        $user     = User::factory()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->remove($staff, $user));
    }

    /** @test */
    public function it_disallows_a_supplier_to_remove_a_confirmed_related_user()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->remove($staff, $supplierUser->user));
    }

    /** @test */
    public function it_disallows_a_supplier_to_remove_a_removed_related_user()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->removed()->create();

        $policy = new UserPolicy();

        $this->assertFalse($policy->remove($staff, $supplierUser->user));
    }

    /** @test */
    public function it_allows_a_supplier_to_removed_an_unconfirmed_related_user()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        $policy = new UserPolicy();
        $this->assertTrue($policy->remove($staff, $supplierUser->user));
    }
}
