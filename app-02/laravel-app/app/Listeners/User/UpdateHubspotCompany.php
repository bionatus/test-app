<?php

namespace App\Listeners\User;

use App\Events\User\CompanyUpdated;
use App\Jobs\Hubspot\UpdateCompany;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateHubspotCompany implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CompanyUpdated $event)
    {
        $companyUser = $event->companyUser();

        UpdateCompany::dispatch($companyUser);
    }
}
