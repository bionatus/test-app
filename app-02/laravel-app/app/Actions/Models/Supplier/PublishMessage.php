<?php

namespace App\Actions\Models\Supplier;

use App\Actions\Models\PubnubChannel\PublishMessage as PubnubChannelPublishMessage;
use App\Models\PubnubChannel;
use App\Models\Supplier;

class PublishMessage extends PubnubChannelPublishMessage
{
    public function __construct(PubnubChannel $pubnubChannel, array $message, Supplier $supplier)
    {
        $lastMessageAtField = 'supplier_last_message_at';
        $senderUuid         = $supplier->getRouteKey();

        parent::__construct($pubnubChannel, $message, $senderUuid, $lastMessageAtField);
    }
}
