<?php

namespace App\Http\Resources\Api\V3\Oem\Part;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PartResource;
use App\Models\Part;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Part $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private PartResource $partResource;

    public function __construct(Part $resource)
    {
        parent::__construct($resource);
        $this->partResource = new PartResource($resource, true);
    }

    public function toArray($request)
    {
        $response = $this->partResource->toArray($request);

        return $response;
    }

    public static function jsonSchema(): array
    {
        return PartResource::jsonSchema();
    }
}
