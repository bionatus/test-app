<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\User\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Events\Order\CanceledByUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\Cancel\StoreRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class CancelController extends Controller
{
    public function __invoke(StoreRequest $request, Order $order)
    {
        $order = App::make(ChangeStatus::class, [
            'order'       => $order,
            'substatusId' => $order->isPending() ? Substatus::STATUS_CANCELED_ABORTED : Substatus::STATUS_CANCELED_REJECTED,
            'detail'      => $this->getCustomDetail($request, $order),
        ])->execute();

        CanceledByUser::dispatch($order);

        $pubnubChannel = (new GetPubnubChannel($order->supplier, $order->user))->execute();
        $message       = PubnubMessageTypes::ORDER_CANCELED;

        if ($bidNumber = $order->bid_number) {
            $message['bid_number'] = $bidNumber;
        }

        App::make(PublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'user'          => $order->user,
        ])->execute();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    private function getCustomDetail(StoreRequest $request, Order $order): string
    {
        if ($this->supplierDoesNotHaveParts($order)) {
            return "Supplier didn't have parts";
        }

        return $request->get(RequestKeys::STATUS_DETAIL);
    }

    private function supplierDoesNotHaveParts($order): bool
    {
        return $order->itemOrders->every(function(ItemOrder $itemOrder) {
            return in_array($itemOrder->status, [
                ItemOrder::STATUS_NOT_AVAILABLE,
                ItemOrder::STATUS_REMOVED,
            ]);
        });
    }
}
