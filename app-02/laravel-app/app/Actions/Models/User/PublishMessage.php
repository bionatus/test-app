<?php

namespace App\Actions\Models\User;

use App;
use App\Actions\Models\PubnubChannel\PublishMessage as PubnubChannelPublishMessage;
use App\Models\PubnubChannel;
use App\Models\User;

class PublishMessage extends PubnubChannelPublishMessage
{
    public function __construct(PubnubChannel $pubnubChannel, array $message, User $user)
    {
        $lastMessageAtField = 'user_last_message_at';
        $senderUuid         = $user->getRouteKey();

        parent::__construct($pubnubChannel, $message, $senderUuid, $lastMessageAtField);
    }
}
