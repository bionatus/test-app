<?php

namespace App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Rules\MoneyFormat;
use App\Rules\OrderDelivery\ValidDateTime;
use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $useStoreAddress         = $this->get(RequestKeys::USE_STORE_ADDRESS);
        $addressRule             = [
            ($this->isCurryDelivery() && !$useStoreAddress) ? 'required' : 'prohibited',
            'string',
        ];
        $addressRuleWithMax      = $addressRule;
        $addressRuleWithMax[]    = 'max:255';
        $addressRuleWithDigits   = $addressRule;
        $addressRuleWithDigits[] = 'digits:5';

        $minutes = $this->isCurryDelivery() ? 30 : 0;

        return [
            RequestKeys::DATE              => [
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
                new ValidEndTime($this->get(RequestKeys::START_TIME)),
                new ValidDateTime($this->get(RequestKeys::DATE), $this->timezone(), $minutes),
            ],
            RequestKeys::FEE               => [
                ($this->isPickup() || $this->isCurryDelivery()) ? 'prohibited' : 'required',
                'numeric',
                'min:0',
                new MoneyFormat(),
            ],
            RequestKeys::USE_STORE_ADDRESS => [
                $this->isCurryDelivery() ? 'required' : 'prohibited',
                'boolean',
            ],
            RequestKeys::ADDRESS           => $addressRuleWithMax,
            RequestKeys::ADDRESS_2         => $addressRuleWithMax,
            RequestKeys::CITY              => $addressRuleWithMax,
            RequestKeys::STATE             => $addressRuleWithMax,
            RequestKeys::ZIP_CODE          => $addressRuleWithDigits,
            RequestKeys::COUNTRY           => $addressRuleWithMax,
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

    private function type(): string
    {
        return $this->order()->orderDelivery->type;
    }

    private function isCurryDelivery(): bool
    {
        return $this->type() === OrderDelivery::TYPE_CURRI_DELIVERY;
    }

    private function isPickup(): bool
    {
        return $this->type() === OrderDelivery::TYPE_PICKUP;
    }
}
