<?php

namespace App\Http\Requests\LiveApi\V1\Order\Delivery\UpdateEta;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Rules\OrderDelivery\ValidDateTime;
use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        $minutes = $this->isCurryDelivery() ? 30 : 0;

        return [
            RequestKeys::DATE => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . Carbon::now($this->timezone())->format('Y-m-d'),
            ],
            RequestKeys::START_TIME => [
                'required',
                'date_format:H:i',
                Rule::in(DeliveryTimeRanges::ALL_START_TIME),
            ],
            RequestKeys::END_TIME => [
                'required',
                'date_format:H:i',
                Rule::in(DeliveryTimeRanges::ALL_END_TIME),
                new ValidDateTime($this->get(RequestKeys::DATE), $this->timezone(), $minutes),
                new ValidEndTime($this->get(RequestKeys::START_TIME)),
            ],
        ];
    }

    private function order(): Order
    {
        /** @var Order $order */
        $order = $this->route(RouteParameters::ORDER);

        return $order;
    }

    private function timezone(): ?string
    {
        return $this->order()->supplier->timezone;
    }

    private function isCurryDelivery(): bool
    {
        return $this->order()->orderDelivery->type === OrderDelivery::TYPE_CURRI_DELIVERY;
    }
}
