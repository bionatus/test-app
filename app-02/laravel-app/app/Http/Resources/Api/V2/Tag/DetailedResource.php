<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use App\Models\Scopes\Latest;
use App\Models\User;
use App\Types\TaggableType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TaggableType $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private User $user;

    public function __construct(TaggableType $resource, User $user)
    {
        parent::__construct($resource);
        $this->user = $user;
    }

    public function toArray($request)
    {
        $resource = new ImagedResource($this->resource);

        $taggable = $this->resource->taggable();

        $taggable->loadCount(['posts', 'followers']);

        $page = $taggable->posts()->with(['media'])->withCount(['comments'])->scoped(new Latest())->paginate();

        return $resource->toArrayWithAdditionalData([
            'following'       => $this->user->isFollowing($taggable),
            'posts_count'     => $taggable->posts_count,
            'followers_count' => $taggable->followers_count,
            'posts'           => new PostCollection($page),
        ]);
    }

    public static function jsonSchema(): array
    {
        $imagedResourceSchema = ImagedResource::jsonSchema();

        return array_merge_recursive($imagedResourceSchema, [
            'properties' => [
                'following'       => ['type' => ['boolean']],
                'posts_count'     => ['type' => ['integer']],
                'followers_count' => ['type' => ['integer']],
                'posts'           => PostCollection::jsonSchema(),
            ],
            'required'   => [
                'following',
                'posts_count',
                'followers_count',
                'posts',
            ],
        ]);
    }
}
