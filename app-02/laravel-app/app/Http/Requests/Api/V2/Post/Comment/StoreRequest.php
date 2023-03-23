<?php

namespace App\Http\Requests\Api\V2\Post\Comment;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\HasTagsCustomMessages;
use App\Http\Requests\FormRequest;
use Config;

class StoreRequest extends FormRequest
{
    use HasTagsCustomMessages;

    public function rules()
    {
        return [
            RequestKeys::MESSAGE       => ['required', 'string', 'max:1000'],
            RequestKeys::IMAGES        => ['nullable', 'array', 'max:3'],
            RequestKeys::IMAGES . '.*' => [
                'bail',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_file_size') / 1024,
            ],
        ];
    }
}
