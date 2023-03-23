<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $resource
 */
class PostResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Post $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $post = $this->resource;
        $post->tags->loadMissing('taggable');

        return [
            'id'         => $post->getRouteKey(),
            'message'    => $post->message,
            'video_url'  => $post->video_url,
            'type'       => $post->type,
            'created_at' => $post->created_at,
            'pinned'     => !!$post->pinned,
            'user'       => new UserResource($post->user),
            'tags'       => new TagCollection($post->tags),
            'images'     => new ImageCollection($post->getMedia(MediaCollectionNames::IMAGES)),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'message'    => ['type' => ['string']],
                'video_url'  => ['type' => ['string', 'null']],
                'type'       => ['type' => ['string']],
                'created_at' => ['type' => ['string']],
                'pinned'     => ['type' => ['boolean']],
                'user'       => UserResource::jsonSchema(),
                'tags'       => TagCollection::jsonSchema(),
                'images'     => ImageCollection::jsonSchema(),
            ],
            'required'             => [
                'id',
                'message',
                'video_url',
                'type',
                'created_at',
                'pinned',
                'user',
                'tags',
                'images',
            ],
            'additionalProperties' => true,
        ];
    }
}
