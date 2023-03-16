<?php

namespace App\Http\Requests\Api\V2\Post\Comment;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use DateTimeInterface;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::CREATED_BEFORE => ['nullable', 'date_format:' . DateTimeInterface::ATOM],
        ];
    }
}
