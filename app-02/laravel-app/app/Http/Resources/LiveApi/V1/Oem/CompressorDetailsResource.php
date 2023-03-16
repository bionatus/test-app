<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class CompressorDetailsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'compressor_type'         => $this->resource->compressor_type,
            'compressor_brand'        => $this->resource->compressor_brand,
            'compressor_model'        => $this->resource->compressor_model,
            'total_compressors'       => $this->resource->total_compressors,
            'compressors_per_circuit' => $this->resource->compressors_per_circuit,
            'compressor_sizes'        => $this->resource->compressor_sizes,
            'rla'                     => $this->resource->rla,
            'lra'                     => $this->resource->lra,
            'capacity_staging'        => $this->resource->capacity_staging,
            'compressor_notes'        => $this->resource->compressor_notes,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'compressor_type'         => ['type' => ['string', 'null']],
                'compressor_brand'        => ['type' => ['string', 'null']],
                'compressor_model'        => ['type' => ['string', 'null']],
                'total_compressors'       => ['type' => ['integer', 'null']],
                'compressors_per_circuit' => ['type' => ['integer', 'null']],
                'compressor_sizes'        => ['type' => ['string', 'null']],
                'rla'                     => ['type' => ['string', 'null']],
                'lra'                     => ['type' => ['string', 'null']],
                'capacity_staging'        => ['type' => ['string', 'null']],
                'compressor_notes'        => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'compressor_type',
                'compressor_brand',
                'compressor_type',
                'compressor_model',
                'total_compressors',
                'compressors_per_circuit',
                'compressor_sizes',
                'rla',
                'lra',
                'capacity_staging',
                'compressor_notes',
            ],
            'additionalProperties' => false,
        ];
    }
}
