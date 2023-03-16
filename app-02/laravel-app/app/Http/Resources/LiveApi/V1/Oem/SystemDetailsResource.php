<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class SystemDetailsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $modelType = $this->resource->modelType;

        return [
            'system_type'    => $this->resource->new_system_type,
            'unit_type'      => $modelType ? $modelType->name : null,
            'tonnage'        => $this->resource->tonnage,
            'total_circuits' => $this->resource->total_circuits,
            'dx_chiller'     => $this->resource->dx_chiller,
            'cooling_type'   => $this->resource->cooling_type,
            'seer'           => $this->resource->seer,
            'eer'            => $this->resource->eer,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'system_type'    => ['type' => ['string', 'null']],
                'unit_type'      => ['type' => ['string', 'null']],
                'tonnage'        => ['type' => ['number', 'null']],
                'total_circuits' => ['type' => ['integer', 'null']],
                'dx_chiller'     => ['type' => ['string', 'null']],
                'cooling_type'   => ['type' => ['string', 'null']],
                'heating_type'   => ['type' => ['string', 'null']],
                'seer'           => ['type' => ['string', 'null']],
                'eer'            => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'system_type',
                'unit_type',
                'tonnage',
                'total_circuits',
                'dx_chiller',
                'cooling_type',
                'seer',
                'eer',
            ],
            'additionalProperties' => false,
        ];
    }
}
