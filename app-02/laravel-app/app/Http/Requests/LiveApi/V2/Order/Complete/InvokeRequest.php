<?php

namespace App\Http\Requests\LiveApi\V2\Order\Complete;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Rules\MoneyFormat;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        $order = $this->route(RouteParameters::ORDER);
        if ($order->orderDelivery->isPickup()) {
            return [
                RequestKeys::TOTAL => ['required', 'numeric', 'min:0', new MoneyFormat()],
            ];
        }

        return [];
    }
}
