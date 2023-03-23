<?php

namespace App\Http\Requests\LiveApi\V1\Setting\BulkNotification;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Setting;
use App\Models\Supplier;
use Lang;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SETTINGS        => [
                'required',
                'bail',
                'array:' . $this->getSupplierNotificationSettings(),
            ],
            RequestKeys::SETTINGS . '.*' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            RequestKeys::SETTINGS . '.array' => Lang::get('validation.custom.array_with_valid_keys'),
        ];
    }

    private function getSupplierNotificationSettings(): string
    {
        return Setting::where('group', Setting::GROUP_NOTIFICATION)
            ->where('applicable_to', Supplier::MORPH_ALIAS)
            ->get()
            ->implode(Setting::routeKeyName(), ',');
    }
}
