<?php

namespace App\Http\Requests\LiveApi\V1\Supplier\BulkHour;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $days = Collection::make(Carbon::getDays())->map(fn(string $day) => strtolower($day));

        return [
            RequestKeys::HOURS             => ['present', 'nullable', 'array'],
            RequestKeys::HOURS . '.*'      => ['array'],
            RequestKeys::HOURS . '.*.day'  => ['required', 'string', Rule::in($days->toArray())],
            RequestKeys::HOURS . '.*.from' => ['required', 'date_format:H:i'],
            RequestKeys::HOURS . '.*.to'   => ['required', 'date_format:H:i', 'after:'.RequestKeys::HOURS . '.*.from'],
        ];
    }
}
