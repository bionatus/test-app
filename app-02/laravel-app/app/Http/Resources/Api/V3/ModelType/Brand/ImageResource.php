<?php

namespace App\Http\Resources\Api\V3\ModelType\Brand;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Brand\ImageResource as BaseImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class ImageResource extends JsonResource implements HasJsonSchema
{
    private BaseImageResource $baseImageResource;

    public function __construct(array $resource)
    {
        parent::__construct($resource);
        $this->baseImageResource = new BaseImageResource($this->resource);
    }

    public function toArray($request)
    {
        return $this->baseImageResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        $schema         = BaseImageResource::jsonSchema();
        $schema['type'] = ['object', 'array', 'null'];

        return $schema;
    }
}
