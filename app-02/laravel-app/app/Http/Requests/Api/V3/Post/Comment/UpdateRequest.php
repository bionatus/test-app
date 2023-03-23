<?php

namespace App\Http\Requests\Api\V3\Post\Comment;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\HasTagsCustomMessages;
use App\Http\Requests\FormRequest;
use App\Models\Media;
use App\Models\User;
use Auth;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    use HasTagsCustomMessages;

    public function rules(): array
    {
        $maxImages          = 3;
        $currentImages      = $this->get(RequestKeys::CURRENT_IMAGES);
        $currentImagesCount = is_array($currentImages) ? count(array_unique($currentImages)) : 0;
        $actualMax          = max($maxImages - $currentImagesCount, 0);

        $hasImages          = Arr::has($this->all(), RequestKeys::IMAGES);
        $currentImagesRules = array_merge($hasImages ? ['present'] : [], ['nullable', 'array', 'max:3']);

        return [
            RequestKeys::MESSAGE               => ['required', 'string', 'max:1000'],
            RequestKeys::USERS                 => ['nullable', 'array'],
            RequestKeys::USERS . '.*'          => [
                'bail',
                'integer',
                Rule::exists(User::tableName(), User::keyName())->whereNot(User::keyName(), Auth::id()),
            ],
            RequestKeys::IMAGES                => ['nullable', 'array', 'max:' . $actualMax],
            RequestKeys::IMAGES . '.*'         => [
                'bail',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_file_size') / 1024,
            ],
            RequestKeys::CURRENT_IMAGES        => $currentImagesRules,
            RequestKeys::CURRENT_IMAGES . '.*' => [
                'bail',
                'uuid',
                Rule::exists(Media::class, 'uuid'),
            ],
        ];
    }
}
