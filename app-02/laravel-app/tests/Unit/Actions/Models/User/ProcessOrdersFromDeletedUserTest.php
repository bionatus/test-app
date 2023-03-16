<?php

namespace Tests\Unit\Actions\Models\User;

use App\Actions\Models\User\ProcessOrdersFromDeletedUser;
use App\Jobs\Supplier\UpdateInboundCounter;
use App\Jobs\Supplier\UpdateLastOrderCanceledAt;
use App\Jobs\User\PublishOrderCanceledMessage;
use App\Models\CompanyUser;
use App\Models\CustomItem;
use App\Models\Order;
use App\Models\OrderLockedData;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Bus;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProcessOrdersFromDeletedUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_the_user_orders_locked_data()
    {
        $user   = User::factory()->create();
        $orders = Order::factory()->pending()->usingUser($user)->count(4)->createQuietly();
        CompanyUser::factory()->usingUser($user);

        $orders->map(function(Order $order) use ($user) {
            $pubnubChannel = PubnubChannel::factory()->usingUser($user)->usingSupplier($order->supplier)->create();
            $order->user->setRelation('pubnubChannels', Collection::make([
                $pubnubChannel,
            ]));
        });

        (new ProcessOrdersFromDeletedUser($user))->execute();

        $orders->each(function(Order $order) {
            $user = $order->user;
            $this->assertDatabaseHas(OrderLockedData::tableName(), [
                'order_id'        => $order->getKey(),
                'user_first_name' => $user->first_name,
                'user_last_name'  => $user->last_name,
                'user_company'    => $user->companyName(),
                'channel'         => $user->pubnubChannels->first()->getRouteKey(),
            ]);
        });
    }

    /** @test
     */
    public function it_updates_custom_items_to_null_values()
    {
        $user               = User::factory()->create();
        $anotherUser        = User::factory()->create();
        $customItemsUser    = CustomItem::factory()->usingUser($user)->count(5)->create();
        $customItemSupplier = CustomItem::factory()->usingUser($anotherUser)->create();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        $customItemsUser->each(function(CustomItem $customItem) {
            $this->assertDatabaseHas(CustomItem::class, [
                'id'           => $customItem->getKey(),
                'creator_type' => null,
                'creator_id'   => null,
            ]);
        });

        $this->assertDatabaseHas(CustomItem::class, [
            'id'           => $customItemSupplier->getKey(),
            'creator_type' => User::MORPH_ALIAS,
            'creator_id'   => $anotherUser->getKey(),
        ]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_updates_orders_with_status_pending_or_pending_approval(
        int $currentSubstatusId,
        int $expectedSubstatus,
        ?string $expectedStatusDetail
    ) {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($currentSubstatusId)->create();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => $expectedSubstatus,
            'detail'       => $expectedStatusDetail,
        ]);
    }

    public function dataProvider(): array
    {
        $statusDetail = 'Cancelled by Deleted account';

        return [
            [Substatus::STATUS_PENDING_REQUESTED, Substatus::STATUS_CANCELED_DELETED_USER, $statusDetail],
            [Substatus::STATUS_PENDING_ASSIGNED, Substatus::STATUS_CANCELED_DELETED_USER, $statusDetail],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_CANCELED_DELETED_USER, $statusDetail],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, Substatus::STATUS_CANCELED_DELETED_USER, $statusDetail],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, Substatus::STATUS_CANCELED_DELETED_USER, $statusDetail],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, Substatus::STATUS_APPROVED_AWAITING_DELIVERY, null],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, null],
            [Substatus::STATUS_APPROVED_DELIVERED, Substatus::STATUS_APPROVED_DELIVERED, null],
            [Substatus::STATUS_COMPLETED_DONE, Substatus::STATUS_COMPLETED_DONE, null],
            [Substatus::STATUS_CANCELED_ABORTED, Substatus::STATUS_CANCELED_ABORTED, null],
            [Substatus::STATUS_CANCELED_CANCELED, Substatus::STATUS_CANCELED_CANCELED, null],
            [Substatus::STATUS_CANCELED_DECLINED, Substatus::STATUS_CANCELED_DECLINED, null],
            [Substatus::STATUS_CANCELED_REJECTED, Substatus::STATUS_CANCELED_REJECTED, null],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, Substatus::STATUS_CANCELED_BLOCKED_USER, null],
        ];
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_a_publish_message_job()
    {
        Bus::fake();

        $user = User::factory()->create();
        Order::factory()->pending()->usingUser($user)->createQuietly();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        Bus::assertDispatched(PublishOrderCanceledMessage::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_an_update_inbound_counter_job()
    {
        Bus::fake();

        $user = User::factory()->create();
        Order::factory()->pending()->usingUser($user)->createQuietly();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        Bus::assertDispatched(UpdateInboundCounter::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_update_inbound_counter_job_one_time_if_supplier_is_the_same_for_the_orders()
    {
        Bus::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->count(5)->createQuietly();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        Bus::assertDispatchedTimes(UpdateInboundCounter::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_an_update_last_order_canceled_at_job()
    {
        Bus::fake();

        $user = User::factory()->create();
        Order::factory()->pending()->usingUser($user)->createQuietly();

        (new ProcessOrdersFromDeletedUser($user))->execute();

        Bus::assertDispatched(UpdateLastOrderCanceledAt::class);
    }
}
