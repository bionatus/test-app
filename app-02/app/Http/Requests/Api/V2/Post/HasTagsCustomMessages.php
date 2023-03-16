<?php

namespace App\Http\Requests\Api\V2\Post;

use App\Constants\RequestKeys;

trait HasTagsCustomMessages
{
    public function messages(): array
    {
        return [
            RequestKeys::TAGS . '.required'         => 'You must select at least one tag.',
            RequestKeys::TAGS . '.required_if'      => 'You must select at least one tag.',
            RequestKeys::TAGS . '.required_without' => 'You must select at least one tag.',
            RequestKeys::TAGS . '.max'              => 'The :attribute may not have more than 5 items.',
            RequestKeys::TAGS . '.*.array'          => 'Each tag in tags must be an array.',
            RequestKeys::IMAGES . '.max'            => 'The :attribute may not have more than :max items.',
            RequestKeys::IMAGES . '.*.file'         => 'Each item in images must be a file.',
            RequestKeys::IMAGES . '.*.mimes'        => 'Each item in images must be of type: :values.',
            RequestKeys::IMAGES . '.*.uploaded'     => 'The file failed to upload.',
            RequestKeys::IMAGES . '.*.max'          => 'Each item in images may not be greater than :max kilobytes.',
            RequestKeys::CURRENT_IMAGES . '.*.uuid' => 'Each item in current images must be an uuid.',
        ];
    }
}
