<?php

namespace App\Http\Requests\Api\V3\Order\Delivery\Curri\Confirm;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Rules\OrderDelivery\ValidDateTime;
use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::DATE       => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . Carbon::now($this->timezone())->format('Y-m-d'),
            ],
            RequestKeys::START_TIME => [
                'required',
                'date_format:H:i',
                Rule::in(DeliveryTimeRanges::ALL_START_TIME),
            ],
            RequestKeys::END_TIME   => [
                'required',
                'date_format:H:i',
                Rule::in(DeliveryTimeRanges::ALL_END_TIME),
                new ValidEndTime($this->get(RequestKeys::START_TIME)),
                new ValidDateTime($this->get(RequestKeys::DATE), $this->timezone()),
            ],
        ];
    }

    private function timezone(): string
    {
        $order = $this->route(RouteParameters::ORDER);

        return $order->supplier->timezone;
    }
}
