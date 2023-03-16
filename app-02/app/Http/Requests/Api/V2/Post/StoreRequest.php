<?php

namespace App\Http\Requests\Api\V2\Post;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\ExistingIncomingRawTag;
use App\Rules\SingleSeries;
use App\Rules\SingleTagTypeMore;
use App\Types\TaggablesCollection;
use Config;

class StoreRequest extends FormRequest
{
    use HasTagsCustomMessages;

    private ExistingIncomingRawTag $existingIncomingRawTagRule;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->existingIncomingRawTagRule = new ExistingIncomingRawTag();
    }

    public function rules(): array
    {
        return [
            RequestKeys::MESSAGE       => ['required', 'string', 'max:1000'],
            RequestKeys::TAGS          => [
                'required',
                'bail',
                'array',
                'max:5',
                new SingleTagTypeMore(),
                new SingleSeries(),
            ],
            RequestKeys::TAGS . '.*'   => ['array', $this->existingIncomingRawTagRule],
            RequestKeys::IMAGES        => ['nullable', 'array', 'max:5'],
            RequestKeys::IMAGES . '.*' => [
                'bail',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_file_size') / 1024,
            ],
        ];
    }

    public function taggables(): TaggablesCollection
    {
        return $this->existingIncomingRawTagRule->taggables();
    }
}
