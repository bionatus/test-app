<?php

namespace App\Rules\Phone;

use App\Constants\RequestKeys;
use App\Models\Phone;
use App\Models\Phone\Scopes\ByCountryCode;
use App\Models\Phone\Scopes\ByNumber;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Request;

class SmsAvailable implements Rule
{
    private ?Phone          $phone = null;
    private CarbonInterface $availableAt;

    public function __construct()
    {
        $this->availableAt = Carbon::now();
    }

    public function passes($attribute, $value): bool
    {
        if (!($countryCode = (int) Request::get(RequestKeys::COUNTRY_CODE))) {
            return true;
        }

        /** @var Phone $phone */
        if (!($phone = Phone::scoped(new ByNumber((int) $value))->scoped(new ByCountryCode($countryCode))->first())) {
            return true;
        }

        $this->phone       = $phone;
        $this->availableAt = $phone->nextRequestAvailableAt();

        return Carbon::now()->gte($this->availableAt);
    }

    public function phone(): ?Phone
    {
        return $this->phone;
    }

    public function message(): string
    {
        return 'The :attribute will be available at ' . $this->availableAt;
    }
}
