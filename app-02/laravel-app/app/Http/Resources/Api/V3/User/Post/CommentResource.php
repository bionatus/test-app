<?php

namespace App\Http\Resources\Api\V3\User\Post;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CommentResource as ModelCommentResource;
use App\Models\Comment;
use Arr;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Comment $resource
 */
class CommentResource extends JsonResource implements HasJsonSchema
{
    const MESSAGE = 'message';
    private ModelCommentResource $modelCommentResource;

    public function __construct(Comment $resource)
    {
        parent::__construct($resource);

        $this->modelCommentResource = new ModelCommentResource($resource);
    }

    public function toArray($request): array
    {
        $modelCommentResource = $this->modelCommentResource->toArray($request);
        Arr::forget($modelCommentResource, self::MESSAGE);

        return $modelCommentResource;
    }

    public static function jsonSchema(): array
    {
        $jsonSchema = ModelCommentResource::jsonSchema(true);
        $jsonSchema = Arr::where($jsonSchema['required'], function($value) {
            return $value !== self::MESSAGE;
        });
        Arr::forget($jsonSchema, 'properties.' . self::MESSAGE);

        return $jsonSchema;
    }
}

