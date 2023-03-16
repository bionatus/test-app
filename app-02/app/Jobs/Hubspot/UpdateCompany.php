<?php

namespace App\Jobs\Hubspot;

use App\Models\CompanyUser;
use App\Services\Hubspot\Hubspot;
use HubSpot\Client\Crm\Contacts\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private CompanyUser $companyUser;

    public function __construct(CompanyUser $companyUser)
    {
        $this->onConnection('database');
        $this->companyUser = $companyUser;
    }

    /**
     * @throws ApiException
     */
    public function handle(Hubspot $hubspot)
    {
        $hubspot->updateUserCompany($this->companyUser);
    }
}
