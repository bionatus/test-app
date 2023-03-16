<?php

namespace App\Http\Requests\Api\V3\Post;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V2\Post\HasTagsCustomMessages;
use App\Http\Requests\FormRequest;
use App\Models\Media;
use App\Models\Post;
use App\Rules\ExistingIncomingRawTag;
use App\Rules\SingleSeries;
use App\Types\TaggablesCollection;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $maxImages          = 5;
        $currentImages      = $this->get(RequestKeys::CURRENT_IMAGES);
        $currentImagesCount = is_array($currentImages) ? count(array_unique($currentImages)) : 0;
        $actualMax          = max($maxImages - $currentImagesCount, 0);

        $hasImages          = Arr::has($this->all(), RequestKeys::IMAGES);
        $currentImagesRules = array_merge($hasImages ? ['present'] : [], ['nullable', 'array', 'max:' . $maxImages]);

        $post      = $this->route(RouteParameters::POST);
        $tagsRules = array_merge((Post::TYPE_OTHER == $post->type) ? ['required'] : [],
            ['bail', 'array', 'max:5', new SingleSeries()]);

        return [
            RequestKeys::MESSAGE               => ['required', 'string', 'max:1000'],
            RequestKeys::TAGS                  => $tagsRules,
            RequestKeys::TAGS . '.*'           => ['array', $this->existingIncomingRawTagRule],
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
            RequestKeys::VIDEO_URL             => ['nullable', 'url', 'max:255'],
        ];
    }

    public function taggables(): TaggablesCollection
    {
        return $this->existingIncomingRawTagRule->taggables();
    }
}
