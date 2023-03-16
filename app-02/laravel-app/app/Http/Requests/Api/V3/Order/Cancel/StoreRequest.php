<?php

namespace App\Http\Requests\Api\V3\Order\Cancel;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $order = $this->route(RouteParameters::ORDER);

        if ($order->isPendingApproval()) {
            return [
                RequestKeys::STATUS_DETAIL => ['bail', 'required', 'string', 'max:255'],
            ];
        }

        return [];
    }
}
