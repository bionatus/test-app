<?php

namespace App\Http\Requests\Api\V2\User\FollowedPost;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use DateTimeInterface;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SEARCH_STRING  => ['nullable', 'string', 'max:1000'],
            RequestKeys::CREATED_BEFORE => ['nullable', 'date_format:' . DateTimeInterface::ATOM],
        ];
    }
}
