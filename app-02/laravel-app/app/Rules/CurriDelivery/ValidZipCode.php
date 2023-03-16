<?php

namespace App\Rules\CurriDelivery;

use App\Models\ForbiddenZipCode;
use App\Models\OrderDelivery;
use Illuminate\Contracts\Validation\Rule;
use Lang;

class ValidZipCode implements Rule
{
    private ?string $zipCode;

    public function __construct(?string $zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function passes($attribute, $value)
    {
        $isCurryDelivery   = $value === OrderDelivery::TYPE_CURRI_DELIVERY;
        $forbiddenZipCodes = ForbiddenZipCode::pluck('zip_code')->toArray();
        $isForbidden       = in_array($this->zipCode, $forbiddenZipCodes);

        if ($isCurryDelivery && (!$this->zipCode || $isForbidden)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return Lang::get('validation.curri_zip_code');
    }
}
