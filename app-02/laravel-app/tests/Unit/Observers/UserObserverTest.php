<?php

namespace Tests\Unit\Observers;

use App;
use App\Actions\Models\User\DeleteRelatedUserModels;
use App\Actions\Models\User\GetTimezone;
use App\Actions\Models\User\ProcessOrdersFromDeletedUser;
use App\Events\User\HubspotFieldUpdated;
use App\Jobs\LogActivity;
use App\Jobs\Supplier\UpdateCustomersCounter;
use App\Jobs\Supplier\UpdateTotalCustomers;
use App\Jobs\User\DeleteFirebaseNode;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Observers\UserObserver;
use Bus;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionException;
use ReflectionProperty;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     *
     * @param array $dirtyAttributes
     *
     * @dataProvider dirtyFieldsDataProvider
     */
    public function it_dispatch_an_event_on_updated(array $dirtyAttributes)
    {
        Bus::fake();
        Event::fake(HubspotFieldUpdated::class);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('requiresHubspotSync')->withNoArgs()->once()->andReturnTrue();

        $observer = new UserObserver();

        Collection::make($dirtyAttributes)->each(function($dirtyAttributes) use ($user) {
            $value     = $dirtyAttributes['value'];
            $isDirty   = $dirtyAttributes['isDirty'];
            $attribute = $dirtyAttributes['attribute'];
            $user->shouldReceive('isDirty')->withArgs([$attribute])->andReturn($isDirty);
            $user->shouldReceive('setAttribute')->withArgs([$attribute, $value])->once();
            $user->shouldReceive('getAttribute')->with($attribute)->andReturn($value);
            $user->shouldReceive('withoutRelations')->withNoArgs()->andReturn($user);
            $user->setAttribute($attribute, $value);
        });

        $observer->updated($user);

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $this->assertSame($user, $event->user());

            return true;
        });
        Bus::assertDispatchedTimes(LogActivity::class, 11);
    }

    public function dirtyFieldsDataProvider(): array
    {
        return [
            [
                [
                    [
                        'attribute' => 'first_name',
                        'value'     => 'John',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'last_name',
                        'value'     => 'Doe',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'public_name',
                        'value'     => 'JohnDoes',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'photo',
                        'value'     => 'foo.jpeg',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'bio',
                        'value'     => 'test Bio',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'address',
                        'value'     => 'fake address',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'address_2',
                        'value'     => 'fake address',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'country',
                        'value'     => 'fake country',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'city',
                        'value'     => 'fake city',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'state',
                        'value'     => 'fake state',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'zip',
                        'value'     => '123465',
                        'isDirty'   => true,
                    ],
                    [
                        'attribute' => 'name',
                        'value'     => 'John',
                        'isDirty'   => false,
                    ],
                ],
            ],
        ];
    }

    /** @test */
    public function it_set_verified_at_when_user_should_be_verified()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('PublicName');
        $user->shouldReceive('isDirty')->withArgs(['verified_at'])->once()->andReturnFalse();
        $user->shouldReceive('verify')->withNoArgs()->once()->andReturnSelf();

        $observer = new UserObserver();
        $observer->updating($user);
    }

    /** @test */
    public function it_set_public_name_on_updating_if_was_not_set()
    {
        $user = User::factory()->make(['public_name' => null]);
        $user->saveQuietly();

        $observer = new UserObserver();
        $observer->updating($user);

        $this->assertNotNull($user->public_name);
    }

    /** @test */
    public function it_calls_delete_related_user_models_action_on_deleting()
    {
        $user = User::factory()->make();
        $user->saveQuietly();

        $action = Mockery::mock(DeleteRelatedUserModels::class);
        $action->shouldReceive('execute')->withNoArgs()->once();
        App::bind(DeleteRelatedUserModels::class, fn() => $action);

        $observer = new UserObserver();
        $observer->deleting($user);
    }

    /** @test */
    public function it_calls_process_user_orders_delete_action_on_deleting()
    {
        $user = User::factory()->make();
        $user->saveQuietly();

        $action = Mockery::mock(ProcessOrdersFromDeletedUser::class);
        $action->shouldReceive('execute')->withNoArgs()->once();
        App::bind(ProcessOrdersFromDeletedUser::class, fn() => $action);

        $observer = new UserObserver();
        $observer->deleting($user);
    }

    /** @test */
    public function it_should_dispatch_delete_firebase_node_on_deleting()
    {
        Bus::fake([DeleteFirebaseNode::class]);

        $user = User::factory()->make();
        $user->saveQuietly();

        $observer = new UserObserver();
        $observer->deleting($user);

        Bus::assertDispatched(function(DeleteFirebaseNode $job) use ($user) {
            $reflectionProperty = new ReflectionProperty(DeleteFirebaseNode::class, 'userId');
            $reflectionProperty->setAccessible(true);

            $this->assertSame($user->getKey(), $reflectionProperty->getValue($job));

            return true;
        });
    }

    /** @test */
    public function it_update_customers_counter_for_each_supplier_on_deleting()
    {
        Bus::fake([DeleteFirebaseNode::class, UpdateCustomersCounter::class, UpdateTotalCustomers::class]);

        $user = User::factory()->make();
        $user->saveQuietly();

        $suppliers = Supplier::factory()->count($times = 5)->createQuietly();
        $suppliers->each(fn(Supplier $supplier) => SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create());

        $otherSupplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();

        $observer = new UserObserver();
        $observer->deleting($user);

        Bus::assertDispatched(function(UpdateCustomersCounter $job) use ($suppliers) {
            $supplierInJob = $this->getPrivateSupplierFromJob(UpdateCustomersCounter::class, $job);

            return $suppliers->pluck(Supplier::keyName())->contains($supplierInJob->getKey());
        });

        Bus::assertNotDispatched(function(UpdateCustomersCounter $job) use ($otherSupplier) {
            $supplierInJob = $this->getPrivateSupplierFromJob(UpdateCustomersCounter::class, $job);

            return $otherSupplier->getKey() === $supplierInJob->getKey();
        });

        Bus::assertDispatched(function(UpdateTotalCustomers $job) use ($suppliers) {
            $supplierInJob = $this->getPrivateSupplierFromJob(UpdateTotalCustomers::class, $job);

            return $suppliers->pluck(Supplier::keyName())->contains($supplierInJob->getKey());
        });

        Bus::assertNotDispatched(function(UpdateTotalCustomers $job) use ($otherSupplier) {
            $supplierInJob = $this->getPrivateSupplierFromJob(UpdateTotalCustomers::class, $job);

            return $otherSupplier->getKey() === $supplierInJob->getKey();
        });

        Bus::assertDispatchedTimes(UpdateCustomersCounter::class, $times);
        Bus::assertDispatchedTimes(UpdateTotalCustomers::class, $times);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_calls_the_get_timezone_action_and_sets_the_corresponding_timezone_on_saving_when_the_country_state_or_zip_is_dirty(
        bool $isDirty
    ) {
        $action = Mockery::mock(GetTimezone::class);
        $action->shouldReceive('execute')->withNoArgs()->times((int) $isDirty)->andReturn($timezone = 'a timezone');
        App::bind(GetTimezone::class, fn() => $action);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('isDirty')->withArgs([['country', 'state', 'zip']])->once()->andReturn($isDirty);
        $user->shouldReceive('setAttribute')->withArgs(['timezone', $timezone])->times((int) $isDirty)->andReturnSelf();

        $observer = new UserObserver();
        $observer->saving($user);
    }

    public function dataProvider(): array
    {
        return [[false], [true]];
    }

    /**
     * @throws ReflectionException
     */
    private function getPrivateSupplierFromJob(string $class, $job): Supplier
    {
        $reflectionProperty = new ReflectionProperty($class, 'supplier');
        $reflectionProperty->setAccessible(true);

        /** @var Supplier $supplierInJob */
        $supplierInJob = $reflectionProperty->getValue($job);

        return $supplierInJob;
    }
}
