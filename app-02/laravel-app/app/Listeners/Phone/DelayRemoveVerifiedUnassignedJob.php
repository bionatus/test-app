<?php

namespace App\Listeners\Phone;

use App\Events\Phone\Verified;
use App\Jobs\Phone\RemoveVerifiedUnassigned;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DelayRemoveVerifiedUnassignedJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Verified $event)
    {
        $phone = $event->phone();
        $ttl = Config::get('communications.phone.verification.ttl');

        RemoveVerifiedUnassigned::dispatch($phone)->delay($phone->created_at->clone()->addMinutes($ttl));
    }
}
