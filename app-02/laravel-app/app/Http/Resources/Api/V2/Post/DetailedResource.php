<?php

namespace App\Http\Resources\Api\V2\Post;

use App\Http\Resources\HasJsonSchema;
use App\Models\Comment\Scopes\Oldest;
use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private BaseResource $baseResource;

    public function __construct(Post $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new BaseResource($resource);
    }

    public function toArray($request)
    {
        $resource = $this->baseResource;
        $page     = $this->resource->comments()->scoped(new Oldest())->paginate();

        return array_replace_recursive($resource->toArray($request), [
            'comments' => new CommentCollection($page),
        ]);
    }

    public static function jsonSchema(): array
    {
        $baseResourceSchema = BaseResource::jsonSchema();

        return array_replace_recursive($baseResourceSchema, [
            'properties' => [
                'comments' => CommentCollection::jsonSchema(),
            ],
            'required'   => [
                'comments',
            ],
        ]);
    }
}
