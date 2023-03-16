<?php

namespace Tests\Unit\Observers\Nova;

use App\Events\Supplier\Selected;
use App\Events\Supplier\Unselected;
use App\Events\User\SuppliersUpdated;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Nova\Observers\SupplierUserObserver;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class SupplierUserObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_stores_updated_on_creation()
    {
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $observer = new SupplierUserObserver();
        $observer->created($supplierUser);

        Event::assertDispatched(SuppliersUpdated::class, function(SuppliersUpdated $event) use ($user) {
            $property = new ReflectionProperty($event, 'user');
            $property->setAccessible(true);
            $this->assertSame($user->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }

    /** @test */
    public function it_dispatches_selected_on_creation()
    {
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $supplier     = Supplier::factory()->createQuietly();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->create();

        $observer = new SupplierUserObserver();
        $observer->created($supplierUser);

        Event::assertDispatched(Selected::class, function(Selected $event) use ($supplier) {
            $property = new ReflectionProperty($event, 'supplier');
            $property->setAccessible(true);
            $this->assertSame($supplier->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }

    /** @test */
    public function it_dispatches_stores_updated_on_deletion()
    {
        Event::fake(SuppliersUpdated::class);

        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $observer = new SupplierUserObserver();
        $observer->deleted($supplierUser);

        Event::assertDispatched(SuppliersUpdated::class, function(SuppliersUpdated $event) use ($user) {
            $property = new ReflectionProperty($event, 'user');
            $property->setAccessible(true);
            $this->assertSame($user->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }

    /** @test */
    public function it_dispatches_unselected_on_deletion()
    {
        Event::fake([SuppliersUpdated::class, Unselected::class]);

        $supplier     = Supplier::factory()->createQuietly();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->create();

        $observer = new SupplierUserObserver();
        $observer->deleted($supplierUser);

        Event::assertDispatched(Unselected::class, function(Unselected $event) use ($supplier) {
            $property = new ReflectionProperty($event, 'supplier');
            $property->setAccessible(true);
            $this->assertSame($supplier->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }
}
