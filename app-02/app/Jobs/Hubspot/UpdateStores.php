<?php

namespace App\Jobs\Hubspot;

use App;
use App\Models\User;
use App\Services\Hubspot\Hubspot;
use HubSpot\Client\Crm\Contacts\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateStores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->onConnection('database');
        $this->user = $user;
    }

    /**
     * @throws ApiException
     */
    public function handle(Hubspot $hubspot)
    {
        $hubspot->updateUserSuppliers($this->user, $this->user->suppliers);
    }
}
