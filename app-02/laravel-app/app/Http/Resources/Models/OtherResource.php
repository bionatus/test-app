<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Other;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Other $resource
 */
class OtherResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Other $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'sort' => $this->resource->sort,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'sort' => ['type' => ['integer', 'null']],
            ],
            'required'             => [
                'sort',
            ],
            'additionalProperties' => false,
        ];
    }
}
