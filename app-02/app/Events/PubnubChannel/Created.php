<?php

namespace App\Events\PubnubChannel;

use App\Models\PubnubChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private PubnubChannel $pubnubChannel;

    public function __construct(PubnubChannel $pubnubChannel)
    {
        $this->pubnubChannel = $pubnubChannel;
    }

    public function pubnubChannel(): PubnubChannel
    {
        return $this->pubnubChannel;
    }
}
