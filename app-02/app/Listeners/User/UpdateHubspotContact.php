<?php

namespace App\Listeners\User;

use App\Events\User\HubspotFieldUpdated;
use App\Jobs\Hubspot\UpdateUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateHubspotContact implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(HubspotFieldUpdated $event)
    {
        $user = $event->user();

        UpdateUser::dispatch($user);
    }
}
