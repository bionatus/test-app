<?php

namespace App\Http\Requests\Api\V3\Post;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\HasTagsCustomMessages;
use App\Http\Requests\FormRequest;
use App\Models\Post;
use App\Rules\ExistingIncomingRawTag;
use App\Rules\SingleSeries;
use App\Rules\SingleTagTypeMore;
use App\Types\TaggablesCollection;
use Config;
use Illuminate\Validation\Rule;

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
            RequestKeys::TYPE          => [
                'nullable',
                'string',
                Rule::in([Post::TYPE_NEEDS_HELP, Post::TYPE_FUNNY, Post::TYPE_OTHER]),
            ],
            RequestKeys::TAGS          => [
                'required_if:type,' . Post::TYPE_OTHER,
                'required_without:type',
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
            RequestKeys::VIDEO_URL     => ['nullable', 'url', 'max:255'],
        ];
    }

    public function taggables(): TaggablesCollection
    {
        return $this->existingIncomingRawTagRule->taggables();
    }
}
