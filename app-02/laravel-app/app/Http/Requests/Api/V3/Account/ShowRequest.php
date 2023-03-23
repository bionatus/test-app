<?php

namespace App\Http\Requests\Api\V3\Account;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class ShowRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::VERSION => ['string', 'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/'],
        ];
    }
}
