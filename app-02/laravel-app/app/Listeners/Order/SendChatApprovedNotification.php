<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage as SupplierPublishMessage;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\OrderEvent;
use App\Models\Order;
use App\Models\PubnubChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendChatApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEvent $event)
    {
        $order         = $event->order();
        $pubnubChannel = (new GetPubnubChannel($order->supplier, $order->user))->execute();

        $this->sendChatToSupplier($pubnubChannel, $order);
        $this->sendChatToUser($pubnubChannel, $order);
    }

    private function sendChatToSupplier(PubnubChannel $pubnubChannel, Order $order)
    {
        $message              = PubnubMessageTypes::ORDER_APPROVED;
        $message['po_number'] = $order->name;

        if ($bidNumber = $order->bid_number) {
            $message['bid_number'] = $bidNumber;
        }

        App::make(UserPublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'user'          => $order->user,
        ])->execute();
    }

    private function sendChatToUser(PubnubChannel $pubnubChannel, Order $order)
    {
        $message = PubnubMessageTypes::ORDER_APPROVED_AUTOMATIC_MESSAGE;

        App::make(SupplierPublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $order->supplier,
        ])->execute();
    }
}
