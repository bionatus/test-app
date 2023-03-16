<?php

namespace App\Http\Requests\LiveApi\V2\Order\Invoice;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::FILE => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf,svg',
                'max:3072',
            ],
        ];
    }
}
