<?php

namespace App\Http\Controllers\Api\V3;

use App;
use App\Actions\Models\Order\LogPendingApprovalFirstView;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage as SupplierPublishMessage;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\Order\Created;
use App\Handlers\OrderDeliveryHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Order\IndexRequest;
use App\Http\Requests\Api\V3\Order\StoreRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Http\Resources\Api\V3\Order\DetailedResource;
use App\Models\CartItem;
use App\Models\CartOrder;
use App\Models\Item;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\OrderedByGroupedStatus;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Scopes\ByName;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\ByUser;
use App\Models\Scopes\ByUuid;
use App\Models\Scopes\Latest;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\Substatus\ByStatus;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use DB;
use Throwable;

class OrderController extends Controller
{
    public function index(IndexRequest $request)
    {
        $user   = Auth::user();
        $query  = Order::query();
        $status = $request->get(RequestKeys::STATUS);

        if ($status) {
            $objStatus   = Status::scoped(new ByName($status, true))->first();
            $substatuses = Substatus::scoped(new ByStatus($objStatus->getKey()))->pluck('id');
            $query->scoped(new ByLastSubstatuses($substatuses->toArray()));
        }

        $query->scoped(new ByUser($user))->scoped(new OrderedByGroupedStatus())->scoped(new Latest())->with([
            'orderDelivery',
            'orderSubstatuses',
            'supplier',
            'user.pubnubChannels' => function($query) use ($user) {
                $query->scoped(new ByUser($user));
            },
        ]);

        $orders = $query->paginate();

        return BaseResource::collection($orders);
    }

    public function show(Order $order)
    {
        App::make(LogPendingApprovalFirstView::class, ['order' => $order, 'user' => Auth::user()])->execute();

        return new DetailedResource($order);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreRequest $request)
    {
        $supplier = $request->supplier();
        $order    = DB::transaction(function() use ($request, $supplier) {
            $oemKey = $request->oem() ? $request->oem()->getKey() : null;
            $user   = Auth::user();

            $this->createSupplierUser($supplier, $user);
            $order = $this->createOrder($supplier->getKey(), $oemKey);
            $this->addItems($order, $request->get(RequestKeys::ITEMS));
            $this->createOrderDelivery($order, $request);

            return $order;
        });

        Created::dispatch($order);

        $order->refresh();

        $this->createCartOrderWithItems($order);

        $user            = $order->user;
        $pubnubChannel   = (new GetPubnubChannel($order->supplier, $user))->execute();
        $supplierMessage = PubnubMessageTypes::NEW_ORDER;
        $userMessage     = $supplier->isInWorkingHours() ? PubnubMessageTypes::NEW_ORDER_IN_WORKING_HOURS : PubnubMessageTypes::NEW_ORDER_NOT_IN_WORKING_HOURS;

        App::make(UserPublishMessage::class, [
            'message'       => $supplierMessage,
            'pubnubChannel' => $pubnubChannel,
            'user'          => $user,
        ])->execute();
        App::make(SupplierPublishMessage::class, [
            'message'       => $userMessage,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $supplier,
        ])->execute();

        return new BaseResource($order);
    }

    private function createOrder(int $supplierKey, int $oemKey = null)
    {
        $order = Order::create([
            'user_id'     => Auth::id(),
            'supplier_id' => $supplierKey,
            'oem_id'      => $oemKey,
        ]);

        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_PENDING_REQUESTED;
        $orderSubStatus->save();

        return $order;
    }

    private function createSupplierUser(Supplier $supplier, User $user)
    {
        SupplierUser::query()->scoped(new ByUser($user))->scoped(new BySupplier($supplier))->firstOrCreate([
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ], [
            'visible_by_user' => false,
        ]);
    }

    private function addItems(Order $order, array $items)
    {
        foreach ($items as $itemInfo) {
            $item = Item::query()->scoped(new ByUuid($itemInfo['uuid']))->first();
            $order->itemOrders()->create([
                'item_id'            => $item->getKey(),
                'quantity'           => $itemInfo['quantity'],
                'quantity_requested' => $itemInfo['quantity'],
            ]);
        }
    }

    private function createOrderDelivery(Order $order, StoreRequest $request)
    {
        $date         = $request->get(RequestKeys::REQUESTED_DATE);
        $startTime    = $request->get(RequestKeys::REQUESTED_START_TIME);
        $endTime      = $request->get(RequestKeys::REQUESTED_END_TIME);
        $deliveryType = $request->get(RequestKeys::TYPE);

        $isCurriDeliveryEnabled = true;

        if ($deliveryType == OrderDelivery::TYPE_CURRI_DELIVERY) {
            $isCurriDeliveryEnabled = $order->supplier->isCurriDeliveryEnabled();
        }

        $dataOrderDelivery = [
            'type'                 => $isCurriDeliveryEnabled ? $deliveryType : OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
            'requested_date'       => $date,
            'requested_start_time' => $startTime,
            'requested_end_time'   => $endTime,
            'date'                 => $date,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'note'                 => $request->get(RequestKeys::NOTE),
        ];

        $dataAddress = [
            'address_1' => $request->get(RequestKeys::DESTINATION_ADDRESS_1),
            'address_2' => $request->get(RequestKeys::DESTINATION_ADDRESS_2),
            'country'   => $request->get(RequestKeys::DESTINATION_COUNTRY),
            'state'     => $request->get(RequestKeys::DESTINATION_STATE),
            'city'      => $request->get(RequestKeys::DESTINATION_CITY),
            'zip_code'  => $request->get(RequestKeys::DESTINATION_ZIP_CODE),
        ];

        $handler = new OrderDeliveryHandler($order);

        $newOrderDelivery   = $handler->createOrUpdateDelivery($dataOrderDelivery);
        $destinationAddress = null;
        if ($newOrderDelivery->isDelivery()) {
            $destinationAddress = $handler->createOrUpdateDestinationAddress($dataAddress);
        }
        $handler->createOrUpdateDeliveryType($destinationAddress);

        $newOrderDelivery->refresh();
    }

    /**
     * @throws Throwable
     */
    private function createCartOrderWithItems(Order $order): void
    {
        $cart = $order->user->cart;
        if (!$cart || $cart->cartItems()->doesntExist()) {
            return;
        }

        DB::transaction(function() use ($order, $cart) {
            $cartOrder = CartOrder::create(['order_id' => $order->id]);
            $cart->cartItems()->each(function(CartItem $cartItem) use ($cartOrder) {
                $cartOrder->cartOrderItems()->create([
                    'item_id'  => $cartItem->item_id,
                    'quantity' => $cartItem->quantity,
                ]);
            });
        });

        $cart->delete();
    }
}
