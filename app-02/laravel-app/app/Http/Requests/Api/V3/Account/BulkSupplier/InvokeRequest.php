<?php

namespace App\Http\Requests\Api\V3\Account\BulkSupplier;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Scopes\ByRouteKeys;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    const MESSAGE_SUPPLIERS_ALL_EXISTS = 'Each item in suppliers must exist.';

    public function rules(): array
    {
        return [
            RequestKeys::SUPPLIERS        => ['required', 'array'],
            RequestKeys::SUPPLIERS . '.*' => [Rule::exists(Supplier::tableName(), Supplier::routeKeyName())],
            RequestKeys::PREFERRED        => [
                'string',
                Rule::exists(Supplier::tableName(), Supplier::routeKeyName()),
                Rule::in($this->request->get(RequestKeys::SUPPLIERS)),
            ],
        ];
    }

    public function messages()
    {
        return [
            RequestKeys::SUPPLIERS . '.*.exists' => self::MESSAGE_SUPPLIERS_ALL_EXISTS,
        ];
    }

    public function suppliers(): Collection
    {
        return Supplier::scoped(new ByRouteKeys($this->get(RequestKeys::SUPPLIERS)))->get();
    }
}
