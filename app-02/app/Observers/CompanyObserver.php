<?php

namespace App\Observers;

use App;
use App\Actions\Models\Company\UpdateCoordinates;
use App\Models\Company;
use Str;

class CompanyObserver
{
    public function creating(Company $company): void
    {
        $company->uuid = Str::uuid();
    }

    public function saved(Company $company): void
    {
        $company->hasValidZipCode() ? $this->gatherCoordinates($company) : $this->resetCoordinates($company);
    }

    private function gatherCoordinates(Company $company): void
    {
        if (($company->isDirty('zip_code') || $company->isDirty('country'))) {
            App::make(UpdateCoordinates::class, ['company' => $company])->execute();
        }
    }

    private function resetCoordinates(Company $company): void
    {
        $company->latitude  = null;
        $company->longitude = null;
    }
}
