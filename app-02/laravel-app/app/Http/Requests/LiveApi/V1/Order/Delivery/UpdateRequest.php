<?php

namespace App\Http\Requests\LiveApi\V1\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Rules\CurriDelivery\ValidZipCode;
use App\Rules\MoneyFormat;
use App\Rules\OrderDelivery\CanChangeToCurri;
use Auth;
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

        return [
            RequestKeys::TYPE              => [
                'required',
                'string',
                Rule::in(OrderDelivery::TYPE_LEGACY_ALL),
                new CanChangeToCurri($this->order()),
                new ValidZipCode(Auth::user()->supplier->zip_code),
            ],
            RequestKeys::VEHICLE_TYPE      => [
                $this->isCurryDelivery() ? 'required' : 'prohibited',
                'string',
                Rule::in(CurriDelivery::VEHICLE_TYPES_ALL),
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
            RequestKeys::ADDRESS_2         => ['nullable', 'string', 'max:255'],
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

    private function isCurryDelivery(): bool
    {
        return $this->get(RequestKeys::TYPE) === OrderDelivery::TYPE_CURRI_DELIVERY;
    }

    private function isPickup(): bool
    {
        return $this->get(RequestKeys::TYPE) === OrderDelivery::TYPE_PICKUP;
    }
}
