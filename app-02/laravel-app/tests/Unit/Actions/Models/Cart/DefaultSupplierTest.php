<?php

namespace Tests\Unit\Actions\Models\Cart;

use App;
use App\Actions\Models\Cart\DefaultSupplier;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultSupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_default_supplier_by_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->onTheNetwork()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $supplier = App::make(DefaultSupplier::class, ['user' => $user])->execute();

        $this->assertInstanceOf(Supplier::class, $supplier);
    }

    /** @test */
    public function it_returns_the_preferred_supplier_as_default_supplier_by_user()
    {
        $user             = User::factory()->create();
        $expectedSupplier = Supplier::factory()->onTheNetwork()->createQuietly();
        $anotherSupplier  = Supplier::factory()->onTheNetwork()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($anotherSupplier)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($expectedSupplier)->create(['preferred' => true]);

        $supplier = App::make(DefaultSupplier::class, ['user' => $user])->execute();

        $this->assertSame($expectedSupplier->getKey(), $supplier->getKey());
    }

    /** @test */
    public function it_returns_the_last_request_ordered_supplier_as_default_supplier_by_user()
    {
        $user             = User::factory()->create();
        $expectedSupplier = Supplier::factory()->onTheNetwork()->createQuietly();
        $anotherSupplier  = Supplier::factory()->onTheNetwork()->createQuietly();
        Order::factory()->usingSupplier($expectedSupplier)->usingUser($user)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($anotherSupplier)->create();

        $supplier = App::make(DefaultSupplier::class, ['user' => $user])->execute();

        $this->assertSame($expectedSupplier->getKey(), $supplier->getKey());
    }

    /** @test */
    public function it_returns_the_nearest_related_supplier_as_default_supplier_by_user()
    {
        $user               = User::factory()->create();
        $company            = Company::factory()->create([
            'country'  => 'US',
            'zip_code' => '12345',
        ]);
        $company->latitude  = 0;
        $company->longitude = 0;
        $company->save();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $suppliers = Supplier::factory()->onTheNetwork()->count(5)->createQuietly([
            'latitude'  => 10,
            'longitude' => 10,
        ]);
        $suppliers->each(function(Supplier $supplier) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
        });
        $nearestSupplier = Supplier::factory()->onTheNetwork()->createQuietly([
            'latitude'  => 1,
            'longitude' => 1,
        ]);
        SupplierUser::factory()->usingUser($user)->usingSupplier($nearestSupplier)->create();

        $supplier = App::make(DefaultSupplier::class, ['user' => $user])->execute();

        $this->assertSame($nearestSupplier->getKey(), $supplier->getKey());
    }

    /** @test */
    public function it_returns_null_if_no_conditions_are_met()
    {
        $user     = User::factory()->create();
        $supplier = App::make(DefaultSupplier::class, ['user' => $user])->execute();

        $this->assertNull($supplier);
    }
}
