<?php

namespace App\Actions\Models\Order;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage as SupplierPublishMessage;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\LegacyApproved;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Substatus;

class Approve
{
    private ?string $name;
    private Order   $order;

    public function __construct(Order $order, ?string $name)
    {
        $this->name  = $name;
        $this->order = $order;
    }

    public function execute(): Order
    {
        $this->order->name = $this->name;
        $this->order->save();
        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $this->order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        $orderSubStatus->save();

        LegacyApproved::dispatch($this->order);

        $pubnubChannel = (new GetPubnubChannel($this->order->supplier, $this->order->user))->execute();

        $this->sendChatToSupplier($pubnubChannel);
        $this->sendChatToUser($pubnubChannel);

        return $this->order;
    }

    private function sendChatToSupplier(PubnubChannel $pubnubChannel)
    {
        $message = PubnubMessageTypes::ORDER_APPROVED;

        if ($this->name) {
            $message['po_number'] = $this->name;
        }

        if ($bidNumber = $this->order->bid_number) {
            $message['bid_number'] = $bidNumber;
        }

        App::make(UserPublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'user'          => $this->order->user,
        ])->execute();
    }

    private function sendChatToUser(PubnubChannel $pubnubChannel)
    {
        $message = PubnubMessageTypes::ORDER_APPROVED_AUTOMATIC_MESSAGE;

        App::make(SupplierPublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $pubnubChannel,
            'supplier'      => $this->order->supplier,
        ])->execute();
    }
}
