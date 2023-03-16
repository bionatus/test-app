<?php

namespace App\Actions\Models\Company;

use App\Jobs\Company\SyncCoordinates;
use App\Models\Company;
use App\Models\Company\Scopes\WithValidCoordinates;
use App\Models\Scopes\ByCountry;
use App\Models\Scopes\ByZipCode;
use App\Models\Scopes\ExceptKey;
use App\Types\CountryDataType;

class UpdateCoordinates
{
    private Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function execute()
    {
        if (!$this->company->hasValidZipCode()) {
            $this->resetCoordinates();

            return;
        }

        if ($existing = $this->findSimilarCoordinatedCompany()) {
            $this->setCoordinatesFromCompany($existing);

            return;
        }

        $this->resetCoordinates();
        SyncCoordinates::dispatch($this->company);
    }

    private function resetCoordinates(): void
    {
        $this->company->latitude  = null;
        $this->company->longitude = null;

        $this->company->saveQuietly();
    }

    private function findSimilarCoordinatedCompany(): ?Company
    {
        return Company::query()
            ->scoped(new ExceptKey($this->company->getKey()))
            ->scoped(new ByCountry(CountryDataType::UNITED_STATES))
            ->scoped(new ByZipCode($this->company->zip_code))
            ->scoped(new WithValidCoordinates())
            ->first();
    }

    private function setCoordinatesFromCompany(Company $company): void
    {
        $this->company->latitude  = $company->latitude;
        $this->company->longitude = $company->longitude;

        $this->company->saveQuietly();
    }
}
