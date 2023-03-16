<?php

namespace App\Listeners\User;

use App\Events\User\UserEventInterface;
use App\Jobs\Hubspot\CreateUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateHubspotContact implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserEventInterface $event)
    {
        $user = $event->user();

        CreateUser::dispatch($user);
    }
}
