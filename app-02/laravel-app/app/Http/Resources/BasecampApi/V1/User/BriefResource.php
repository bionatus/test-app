<?php

namespace App\Http\Resources\BasecampApi\V1\User;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class BriefResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $user = $this->resource;
        /** @var Media $media */
        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'         => $this->getRouteKey(),
            'avatar'     => $media ? new ImageResource($media) : null,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties'           => [
                'id'         => ['type' => ['integer']],
                'avatar'     => ImageResource::jsonSchema(true),
                'first_name' => ['type' => ['string']],
                'last_name'  => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'avatar',
                'first_name',
                'last_name',
            ],
            'additionalProperties' => false,
        ];
    }
}
