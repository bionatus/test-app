<?php

namespace App\Http\Requests\Api\V2\Post\Solution;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V2\Post\HasTagsCustomMessages;
use App\Http\Requests\FormRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    use HasTagsCustomMessages;

    public function rules()
    {
        /** @var Post $post */
        $post = $this->route(RouteParameters::POST);

        return [
            RequestKeys::SOLUTION => [
                'required',
                'uuid',
                Rule::exists(Comment::tableName(), Comment::routeKeyName())->where('post_id', $post->getKey()),
            ],
        ];
    }
}
