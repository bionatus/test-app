<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class ManualsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'bluon_guidelines' => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_GUIDELINES)),
            'diagnostic'       => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_DIAGNOSTIC)),
            'iom'              => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_IOM)),
            'misc'             => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_MISCELLANEOUS)),
            'product_data'     => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_PRODUCT_DATA)),
            'service_facts'    => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_SERVICE_FACTS)),
            'wiring_diagram'   => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_WIRING_DIAGRAM)),
            'controls_manuals' => ManualResource::collection($this->resource->manualType(Oem::MANUAL_TYPE_CONTROLS_MANUALS)),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'bluon_guidelines' => ManualResource::jsonSchema(),
                'diagnostic'       => ManualResource::jsonSchema(),
                'iom'              => ManualResource::jsonSchema(),
                'misc'             => ManualResource::jsonSchema(),
                'product_data'     => ManualResource::jsonSchema(),
                'service_facts'    => ManualResource::jsonSchema(),
                'wiring_diagram'   => ManualResource::jsonSchema(),
                'controls_manuals' => ManualResource::jsonSchema(),
            ],
            'required'             => [
                'bluon_guidelines',
                'diagnostic',
                'iom',
                'misc',
                'product_data',
                'service_facts',
                'wiring_diagram',
                'controls_manuals',
            ],
            'additionalProperties' => false,
        ];
    }
}
