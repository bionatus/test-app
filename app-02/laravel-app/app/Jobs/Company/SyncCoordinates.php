<?php

namespace App\Jobs\Company;

use App\Models\Company;
use App\Types\CountryDataType;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Geocoder\Geocoder;

class SyncCoordinates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Company $company;

    public function __construct(Company $company)
    {
        $this->onConnection('database');
        $this->company = $company;
    }

    public function handle(Geocoder $geocoder): void
    {
        if ($this->company->hasValidCoordinates()) {
            return;
        }

        if (!$this->company->hasValidZipcode()) {
            return;
        }
        $geocoder->setCountry(CountryDataType::UNITED_STATES);
        try {
            $response = $geocoder->getCoordinatesForAddress($this->company->zip_code);
            if (Geocoder::RESULT_NOT_FOUND === $response['accuracy']) {
                return;
            }

            $this->company->latitude  = $response['lat'];
            $this->company->longitude = $response['lng'];
            $this->company->saveQuietly();
        } catch (Exception $exception) {
            // Silently ignored
        }
    }
}

