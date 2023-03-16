<?php

namespace App\Listeners\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\Delivery\Curri\OnRoute;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCurriDeliveryOnRoutePubnubMessage implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OnRoute $event)
    {
        $curriDelivery           = $event->curriDelivery();
        $order                   = $curriDelivery->orderDelivery->order;
        $supplier                = $order->supplier;
        $message                 = PubnubMessageTypes::CURRI_DELIVERY_ON_ROUTE;
        $message['tracking_url'] = $curriDelivery->tracking_url;
        $pubnubChannel           = App::make(GetPubnubChannel::class, [
            'supplier' => $supplier,
            'user'     => $order->user,
        ])->execute();

        App::make(PublishMessage::class, [
            'pubnubChannel' => $pubnubChannel,
            'message'       => $message,
            'supplier'      => $supplier,
        ])->execute();
    }
}
