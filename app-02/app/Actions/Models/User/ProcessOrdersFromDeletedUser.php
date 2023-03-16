<?php

namespace App\Actions\Models\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Jobs\Supplier\UpdateInboundCounter;
use App\Jobs\Supplier\UpdateLastOrderCanceledAt;
use App\Jobs\User\PublishOrderCanceledMessage;
use App\Models\Order;
use App\Models\OrderLockedData;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByKeys;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Arr;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProcessOrdersFromDeletedUser
{
    const CHUNK = 100;
    private User       $user;
    private Collection $orderLockedData;
    private Collection $ordersToUpdate;

    public function __construct(User $user)
    {
        $this->user            = $user;
        $this->orderLockedData = Collection::make([]);
        $this->ordersToUpdate  = Collection::make([]);
    }

    public function execute(): void
    {
        $this->user->orders()->cursor()->each(function(Order $order) {
            $pubnubChannel = (new GetPubnubChannel($order->supplier, $this->user))->execute();

            $this->addOrderLockedDataItem($order, $pubnubChannel);
            $this->addOrderToUpdate($order, $pubnubChannel);
        });

        $this->createOrderLockedData();
        $this->updateCustomItems();
        $this->updateOrders();
        $this->dispatchJobs();
    }

    private function addOrderLockedDataItem(Order $order, PubnubChannel $pubnubChannel): void
    {
        $this->orderLockedData->push([
            'order_id'        => $order->getKey(),
            'user_first_name' => $this->user->first_name,
            'user_last_name'  => $this->user->last_name,
            'user_company'    => $this->user->companyName(),
            'channel'         => $pubnubChannel->getRouteKey(),
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),
        ]);
    }

    private function addOrderToUpdate(Order $order, PubnubChannel $pubnubChannel): void
    {
        if ($order->isPending() || $order->isPendingApproval()) {
            $this->ordersToUpdate->push([
                'id'             => $order->getKey(),
                'bid_number'     => $order->bid_number,
                'pubnub_channel' => $pubnubChannel,
                'supplier'       => $order->supplier,
            ]);
        }
    }

    private function createOrderLockedData(): void
    {
        $this->orderLockedData->chunk(self::CHUNK)->each(function(Collection $orderLockedDataChunk) {
            OrderLockedData::insert($orderLockedDataChunk->toArray());
        });
    }

    private function dispatchJobs(): void
    {
        $this->ordersToUpdate->each(function(array $item) {
            $bidNumber     = Arr::get($item, 'bid_number');
            $pubnubChannel = Arr::get($item, 'pubnub_channel');
            $supplier      = Arr::get($item, 'supplier');

            PublishOrderCanceledMessage::dispatch($pubnubChannel, $bidNumber);
            UpdateLastOrderCanceledAt::dispatch($supplier);
        });

        $suppliers = $this->ordersToUpdate->pluck('supplier')->unique();
        $suppliers->each(function(Supplier $supplier) {
            UpdateInboundCounter::dispatch($supplier);
        });
    }

    private function updateCustomItems(): void
    {
        $this->user->customItems()->update([
            'creator_type' => null,
            'creator_id'   => null,
        ]);
    }

    private function updateOrders(): void
    {
        $orderIdsToUpdate = $this->ordersToUpdate->pluck('id')->toArray();

        Order::scoped(new ByKeys($orderIdsToUpdate))->cursor()->each(function(Order $order) {
            $order->orderSubstatuses()->create([
                'substatus_id' => Substatus::STATUS_CANCELED_DELETED_USER,
                'detail'       => 'Cancelled by Deleted account',
            ]);
        });
    }
}
