<?php

namespace App\Http\Resources\Api\V3\Account\Supplier\Channel;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource as BaseResource;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource implements HasJsonSchema
{
    private BaseResource $baseResource;

    public function __construct(Media $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseResource::jsonSchema(true);
    }
}
