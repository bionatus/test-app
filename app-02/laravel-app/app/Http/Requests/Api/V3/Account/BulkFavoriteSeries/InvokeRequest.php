<?php

namespace App\Http\Requests\Api\V3\Account\BulkFavoriteSeries;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Series;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SERIES        => ['present', 'nullable', 'array'],
            RequestKeys::SERIES . '.*' => [Rule::exists(Series::tableName(), Series::routeKeyName())],
        ];
    }

    public function messages()
    {
        return [
            RequestKeys::SERIES . '.*.exists' => 'Each item in series must exist.',
        ];
    }
}
