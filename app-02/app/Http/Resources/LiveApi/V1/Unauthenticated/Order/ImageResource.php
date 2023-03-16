<?php

namespace App\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource as BaseImageResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Media $resource
 */
class ImageResource extends JsonResource implements HasJsonSchema
{
    private BaseImageResource $baseImageResource;

    public function __construct(Media $resource)
    {
        parent::__construct($resource);
        $this->baseImageResource = new BaseImageResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseImageResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseImageResource::jsonSchema(true);
    }
}
