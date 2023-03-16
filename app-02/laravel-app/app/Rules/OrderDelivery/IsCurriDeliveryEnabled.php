<?php

namespace App\Rules\OrderDelivery;

use App;
use App\Models\Supplier;
use Illuminate\Contracts\Validation\Rule;

class IsCurriDeliveryEnabled implements Rule
{
    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function passes($attribute, $value): bool
    {
        return $this->supplier->isCurriDeliveryEnabled();
    }

    public function message(): string
    {
        return "The curri delivery is not enabled for this supplier.";
    }
}
