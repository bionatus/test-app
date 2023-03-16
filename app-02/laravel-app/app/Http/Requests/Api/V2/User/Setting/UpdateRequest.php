<?php

namespace App\Http\Requests\Api\V2\User\Setting;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Setting;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Setting $setting */
        $setting = $this->route(RouteParameters::SETTING_USER) ?? new Setting();

        $rules = [
            RequestKeys::VALUE => ['required'],
        ];

        switch ($setting->type) {
            case Setting::TYPE_BOOLEAN:
                $rules[RequestKeys::VALUE][] = 'boolean';
                break;
            case Setting::TYPE_STRING:
                $rules[RequestKeys::VALUE][] = 'string';
                break;
            case Setting::TYPE_INTEGER:
                $rules[RequestKeys::VALUE][] = 'integer';
                break;
            case Setting::TYPE_DOUBLE:
                $rules[RequestKeys::VALUE][] = 'numeric';
                break;
            default:
                break;
        }

        return $rules;
    }
}
