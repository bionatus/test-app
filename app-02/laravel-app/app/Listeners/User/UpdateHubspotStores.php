<?php

namespace App\Listeners\User;

use App\Events\User\SuppliersUpdated;
use App\Jobs\Hubspot\UpdateStores;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateHubspotStores implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SuppliersUpdated $event)
    {
        $user = $event->user();

        UpdateStores::dispatch($user);
    }
}
