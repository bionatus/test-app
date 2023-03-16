<?php

namespace App\Http\Requests\Api\V4\Order\Delivery;

use App;
use App\Actions\Models\Supplier\GetWorkingHoursByDays;
use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Supplier;
use App\Rules\OrderDelivery\AreInRangeOfWorkingHours;
use App\Rules\OrderDelivery\Is30MinutesBefore;
use App\Rules\ProhibitedAttribute;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PickupRequest extends FormRequest
{
    public function rules()
    {
        $order        = $this->route(RouteParameters::ORDER);
        $date         = $this->get(RequestKeys::REQUESTED_DATE) ?? '';
        $startTime    = $this->get(RequestKeys::REQUESTED_START_TIME) ?? '';
        $supplier     = $order->supplier;
        $workingHours = $this->getWorkingHours($supplier, $date . ' ' . $startTime);

        return [
            RequestKeys::REQUESTED_DATE       => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . Carbon::now($supplier->timezone)->format('Y-m-d'),
            ],
            RequestKeys::REQUESTED_START_TIME => [
                'required',
                'date_format:H:i',
                Rule::in(array_keys($workingHours['start_time'])),
            ],
            RequestKeys::REQUESTED_END_TIME   => [
                'required',
                'date_format:H:i',
                Rule::in(array_keys($workingHours['end_time'])),
                new AreInRangeOfWorkingHours($workingHours['time_ranges'], $startTime),
                new Is30MinutesBefore($workingHours, $supplier, $date),
            ],
            RequestKeys::SHIPMENT_PREFERENCE  => [new ProhibitedAttribute()],
        ];
    }

    private function getWorkingHours(Supplier $supplier, string $date): array
    {
        $params = ['supplier' => $supplier, 'date' => $date];

        return (App::make(GetWorkingHoursByDays::class, $params))->execute();
    }
}
