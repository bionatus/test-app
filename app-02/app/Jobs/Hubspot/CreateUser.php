<?php

namespace App\Jobs\Hubspot;

use App;
use App\Models\User;
use App\Services\Hubspot\Hubspot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->onConnection('database');
        $this->user = $user;
    }

    public function handle()
    {
        $hubspot = App::make(Hubspot::class);

        $contact = $hubspot->createContact($this->user);

        if ($contact && $contact->getId()) {
            $this->user->hubspot_id = $contact->getId();
            $this->user->save();

            $company = $hubspot->createCompany($this->user, $contact->getId());

            if ($company) {
                $hubspot->associateCompanyContact($company->getId(), $contact->getId());
            }
        }
    }
}
