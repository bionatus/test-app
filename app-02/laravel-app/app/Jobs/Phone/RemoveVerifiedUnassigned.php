<?php

namespace App\Jobs\Phone;

use App\Models\Phone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveVerifiedUnassigned implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Phone $phone;

    public function __construct(Phone $phone)
    {
        $this->phone = $phone;
        $this->onConnection('database');
    }

    public function handle()
    {
        if ($this->phone->isVerified() && !$this->phone->isAssigned()) {
            $this->phone->delete();
        }
    }
}
