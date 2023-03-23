<?php

namespace Tests\Unit\Jobs\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\UserConfirmationRequired;
use App\Jobs\Order\Delivery\Curri\DispatchUserConfirmationRequired;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DispatchUserConfirmationRequiredTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DispatchUserConfirmationRequired::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
        $this->assertTrue($reflection->implementsInterface(ShouldBeUnique::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new DispatchUserConfirmationRequired(new Order(), Carbon::now());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_generates_unique_id_for_the_same_order_delivery()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create(['working_on_it' => 'fake name']);

        $now           = Carbon::now($supplier->timezone);
        $startTime     = Carbon::createFromTime(9)->format('H:i');
        $endTime       = Carbon::createFromTime(17)->format('H:i');
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $now,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);

        $destinationAddress = Address::factory()->create(['address_2' => 'fake address 2']);
        CurriDelivery::factory()
            ->usingDestinationAddress($destinationAddress)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $job   = new DispatchUserConfirmationRequired($order, $now);
        $jobId = $job->uniqueId();

        $id = $order->id . $now->format('Ymd') . '090000' . '170000';

        $this->assertSame($jobId, $id);
    }

    /** @test */
    public function it_uses_database_driver_as_cache_lock_repository()
    {
        $this->refreshDatabaseForSingleTest();

        $now           = Carbon::now();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $job = new DispatchUserConfirmationRequired($order, $now);

        $this->assertSame(Cache::driver('database'), $job->uniqueVia());
    }

    /**
     * @test
     * @dataProvider dateDiffProvider
     */
    public function it_dispatches_user_confirmation_required_if_delivery_time_is_not_changed(bool $expected, int $diff)
    {
        $this->refreshDatabaseForSingleTest();

        Event::fake(UserConfirmationRequired::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        $order    = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->approved()
            ->create(['working_on_it' => 'fake name']);

        $now           = Carbon::now($supplier->timezone);
        $startTime     = $now->format('H:i');
        $endTime       = Carbon::createFromTime(17)->format('H:i');
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $now,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);

        $destinationAddress = Address::factory()->create(['address_2' => 'fake address 2']);
        CurriDelivery::factory()
            ->usingDestinationAddress($destinationAddress)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $job = new DispatchUserConfirmationRequired($order, $now->subHours($diff)->startOfHour());
        $job->handle();

        if ($expected) {
            Event::assertDispatched(UserConfirmationRequired::class);
        } else {
            Event::assertNotDispatched(UserConfirmationRequired::class);
        }
    }

    public function dateDiffProvider(): array
    {
        return [
            [true, 0],
            [false, 1],
            [false, -1],
        ];
    }

    /**
     * @test
     * @dataProvider statusProvider
     */
    public function it_dispatches_user_confirmation_required_if_order_is_approved(int $substatusId, bool $expected)
    {
        $this->refreshDatabaseForSingleTest();

        Event::fake(UserConfirmationRequired::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create([
            'working_on_it' => 'fake name',
        ]);
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $now           = Carbon::now($supplier->timezone);
        $startTime     = $now->format('H:i');
        $endTime       = Carbon::createFromTime(17)->format('H:i');
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $now,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);

        $destinationAddress = Address::factory()->create(['address_2' => 'fake address 2']);
        CurriDelivery::factory()
            ->usingDestinationAddress($destinationAddress)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $job = new DispatchUserConfirmationRequired($order, $now->startOfHour());
        $job->handle();

        if ($expected) {
            Event::assertDispatched(UserConfirmationRequired::class);
        } else {
            Event::assertNotDispatched(UserConfirmationRequired::class);
        }
    }

    public function statusProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
        ];
    }
}
