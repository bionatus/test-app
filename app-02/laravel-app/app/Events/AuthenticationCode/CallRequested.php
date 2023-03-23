<?php

namespace App\Events\AuthenticationCode;

use App\Models\Phone;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Phone $phone;

    public function __construct(Phone $phone)
    {
        $this->phone = $phone;
    }

    public function phone(): Phone
    {
        return $this->phone;
    }
}
