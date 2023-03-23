<?php

namespace App\Http\Requests\Api\V2\Post;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Post;
use App\Rules\ExistingIncomingRawTag;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
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
        $this->existingIncomingRawTagRule = App::make(ExistingIncomingRawTag::class);
    }

    public function rules()
    {
        return [
            RequestKeys::SEARCH_STRING  => ['nullable', 'string', 'max:1000'],
            RequestKeys::TAGS           => ['nullable', 'bail', 'array'],
            RequestKeys::TAGS . '.*'    => ['array', $this->existingIncomingRawTagRule],
            RequestKeys::CREATED_BEFORE => ['nullable', 'date_format:' . DateTimeInterface::ATOM],
            RequestKeys::TYPE           => [
                'nullable',
                'string',
                Rule::in([Post::TYPE_NEEDS_HELP, Post::TYPE_FUNNY, Post::TYPE_OTHER]),
            ],
        ];
    }

    public function taggableTypes(): Collection
    {
        return $this->existingIncomingRawTagRule->taggableTypes();
    }
}
