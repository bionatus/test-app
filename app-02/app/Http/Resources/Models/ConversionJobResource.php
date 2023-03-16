<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ConversionJob;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class ConversionJobResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ConversionJob $conversionJob)
    {
        parent::__construct($conversionJob);
    }

    public function toArray($request)
    {
        $image = $this->resource->image;
        return [
            'control'  => $this->resource->control,
            'standard' => $this->resource->standard,
            'optional' => $this->resource->optional,
            'retrofit' => $this->resource->retrofit,
            'image'    => !empty($image) ? asset(Storage::url($image)) : '',
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'       => ['object'],
            'properties' => [
                'control'  => ['type' => ['string']],
                'standard' => ['type' => ['string']],
                'optional' => ['type' => ['string']],
                'retrofit' => ['type' => ['string']],
                'image'    => ['type' => ['string']],
            ],
            'required' => [
                'control',
                'standard',
                'optional',
                'retrofit',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
