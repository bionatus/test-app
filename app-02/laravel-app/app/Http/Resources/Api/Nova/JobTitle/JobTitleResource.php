<?php

namespace App\Http\Resources\Api\Nova\JobTitle;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $resource
 */
class JobTitleResource extends JsonResource implements HasJsonSchema
{
    public function __construct(string $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'value'   => $this->resource,
            'display' => $this->resource,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'value'   => ['type' => ['string']],
                'display' => ['type' => ['string']],
            ],
            'required'             => [
                'value',
                'display',
            ],
            'additionalProperties' => false,
        ];
    }
}
