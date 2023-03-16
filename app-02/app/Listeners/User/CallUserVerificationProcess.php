<?php

namespace App\Listeners\User;

use App\Events\User\SuppliersUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CallUserVerificationProcess implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SuppliersUpdated $event)
    {
        $user = $event->user();

        $user->verify();
        $user->save();
    }
}
