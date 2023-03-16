<?php

namespace App\Http\Resources\BasecampApi\V1\SupportCall;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource as BaseResource;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class OemResource extends JsonResource implements HasJsonSchema
{
    private BaseResource $oemResource;

    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
        $this->oemResource = new BaseResource($resource);
    }

    public function toArray($request)
    {
        $resource                = $this->oemResource->toArray($request);
        $resource['refrigerant'] = $this->resource->refrigerant;

        return $resource;
    }

    public static function jsonSchema(): array
    {
        $schema                              = BaseResource::jsonSchema();
        $schema['type'][]                    = 'null';
        $schema['properties']['refrigerant'] = ['type' => ['string', 'null']];
        $schema['required'][]                = 'refrigerant';

        return $schema;
    }
}
