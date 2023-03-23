<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\SentForApproval;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\OrderSubstatus;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\ByUser;
use App\Models\Substatus;
use Config;
use Str;
use Symfony\Component\HttpFoundation\Response;

class SendForApprovalController extends Controller
{
    public function __invoke(Order $order)
    {
        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_PENDING_APPROVAL_FULFILLED;
        $orderSubStatus->save();

        SentForApproval::dispatch($order);

        $userOldestPendingOrder = Order::scoped(new BySupplier($order->supplier))
            ->scoped(new ByUser($order->user))
            ->scoped(new ByLastSubstatuses([Substatus::STATUS_PENDING_REQUESTED]))
            ->oldest(Order::CREATED_AT)
            ->first();
        $order->user->setAttribute('oldestPendingOrder', $userOldestPendingOrder);

        $pubnubChannel = (new GetPubnubChannel($order->supplier, $order->user))->execute();
        $textMessage   = PubnubMessageTypes::ORDER_SENT_FOR_APPROVAL;
        $linkMessage   = PubnubMessageTypes::ORDER_SENT_FOR_APPROVAL_LINK;
        $orderUuid     = $order->getRouteKey();
        $shareLink     = Config::get('live.url') . Str::replace('{order}', $orderUuid,
                Config::get('live.order.summary'));

        $linkMessage['orderId']    = $orderUuid;
        $linkMessage['order_id']   = $orderUuid;
        $linkMessage['shareLink']  = $shareLink;
        $linkMessage['share_link'] = $shareLink;

        App::make(PublishMessage::class, [
            'message'       => $textMessage,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $order->supplier,
        ])->execute();
        App::make(PublishMessage::class, [
            'message'       => $linkMessage,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $order->supplier,
        ])->execute();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
