<?php

namespace App\Actions\Models\PubnubChannel;

use App;
use App\Models\PubnubChannel;
use Exception;
use Illuminate\Support\Carbon;
use PubNub\PubNub;
use Spatie\QueueableAction\QueueableAction;

abstract class PublishMessage
{
    use QueueableAction;

    protected string        $lastMessageAtField;
    protected array         $message;
    protected PubnubChannel $pubnubChannel;
    protected string        $senderUuid;

    public function __construct(
        PubnubChannel $pubnubChannel,
        array $message,
        string $senderUuid,
        string $lastMessageAtField
    ) {
        $this->lastMessageAtField = $lastMessageAtField;
        $this->message            = $message;
        $this->pubnubChannel      = $pubnubChannel;
        $this->senderUuid         = $senderUuid;
    }

    public function execute()
    {
        $channel = $this->pubnubChannel->getRouteKey();

        try {
            $pubnub = App::make(PubNub::class, ['uuid' => $this->senderUuid]);
            $pubnub->publish()->channel($channel)->message($this->message)->sync();
            $this->pubnubChannel->update([$this->lastMessageAtField => Carbon::now()]);
        } catch (Exception $exception) {
            // Silently ignored
        }
    }
}
