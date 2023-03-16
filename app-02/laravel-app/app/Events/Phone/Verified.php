<?php

namespace App\Events\Phone;

use App\Models\Phone;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Verified
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
