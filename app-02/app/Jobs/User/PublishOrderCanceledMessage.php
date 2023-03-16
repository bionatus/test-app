<?php

namespace App\Jobs\User;

use App;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Models\PubnubChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishOrderCanceledMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?string       $bidNumber;
    private PubnubChannel $pubnubChannel;

    public function __construct(PubnubChannel $pubnubChannel, ?string $bidNumber)
    {
        $this->bidNumber     = $bidNumber;
        $this->pubnubChannel = $pubnubChannel;
        $this->onConnection('database');
    }

    public function handle()
    {
        $message               = PubnubMessageTypes::ORDER_CANCELED;
        $message['bid_number'] = $this->bidNumber;

        App::make(UserPublishMessage::class, [
            'message'       => $message,
            'pubnubChannel' => $this->pubnubChannel,
            'user'          => $this->pubnubChannel->user,
        ])->execute();
    }
}
