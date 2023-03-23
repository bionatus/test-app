<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\WarningResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @property Collection $resource
 */
class WarningCollection extends ResourceCollection implements HasJsonSchema
{
    public function __construct(Collection $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'data' => WarningResource::collection($this->resource),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data' => [
                    'type'  => 'array',
                    'items' => WarningResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
