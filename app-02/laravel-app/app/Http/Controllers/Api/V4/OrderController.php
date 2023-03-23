<?php

namespace App\Http\Controllers\Api\V4;

use App;
use App\Actions\Models\Order\LogPendingApprovalFirstView;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage as SupplierPublishMessage;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\StoreRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Http\Resources\Api\V4\Order\DetailedResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartOrder;
use App\Models\Order;
use App\Models\Order\Scopes\OrderedByGroupedStatus;
use App\Models\OrderSubstatus;
use App\Models\Scopes\ByUser;
use App\Models\Scopes\Latest;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use DB;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /** @var User $user */
        $orders = Order::scoped(new ByUser($user))->scoped(new OrderedByGroupedStatus())->scoped(new Latest())->with([
            'orderDelivery',
            'supplier',
            'user.pubnubChannels' => function($query) use ($user) {
                $query->scoped(new ByUser($user));
            },
        ])->paginate();

        return BaseResource::collection($orders);
    }

    public function show(Order $order)
    {
        App::make(LogPendingApprovalFirstView::class, ['order' => $order, 'user' => Auth::user()])->execute();

        return new DetailedResource($order);
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreRequest $request)
    {
        /** @var User $user */
        $user     = Auth::user();
        $cart     = $user->cart;
        $supplier = $cart->supplier;

        $order = DB::transaction(function() use ($request, $supplier, $cart, $user) {
            $oemKey     = $request->oem() ? $request->oem()->getKey() : null;
            $companyKey = $request->company() ? $request->company()->getKey() : null;

            $this->createSupplierUser($supplier, $user);
            $order = $this->createOrder($supplier->getKey(), $oemKey, $companyKey);
            $this->addItems($cart, $order);

            return $order;
        });

        Created::dispatch($order);

        $this->createCartOrderWithItems($order);

        $order->refresh();

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

    private function createOrder(int $supplierKey, ?int $oemKey = null, ?int $companyKey = null): Order
    {
        $order = Order::create([
            'user_id'     => Auth::id(),
            'supplier_id' => $supplierKey,
            'oem_id'      => $oemKey,
            'company_id'  => $companyKey,
        ]);

        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_PENDING_REQUESTED;
        $orderSubStatus->save();

        return $order;
    }

    private function addItems(Cart $cart, Order $order)
    {
        $cart->cartItems()->each(function(CartItem $cartItem) use ($order) {
            $order->itemOrders()->create([
                'item_id'            => $cartItem->item_id,
                'quantity'           => $cartItem->quantity,
                'quantity_requested' => $cartItem->quantity,
                'created_at'         => $order->created_at,
                'updated_at'         => $order->updated_at,
            ]);
        });
    }

    private function createSupplierUser(Supplier $supplier, User $user)
    {
        SupplierUser::query()->firstOrCreate([
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ], [
            'visible_by_user' => false,
        ]);
    }

    /**
     * @throws \Throwable
     */
    private function createCartOrderWithItems(Order $order): void
    {
        $cart = $order->user->cart;

        $cartOrder = CartOrder::create(['order_id' => $order->id]);
        $cart->cartItems()->each(function(CartItem $cartItem) use ($cartOrder) {
            $cartOrder->cartOrderItems()->create([
                'item_id'  => $cartItem->item_id,
                'quantity' => $cartItem->quantity,
            ]);
        });
        $cart->delete();
    }
}
