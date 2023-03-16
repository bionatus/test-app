<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ControlBoard;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ControlBoard $resource
 */
class ControlBoardResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ControlBoard $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'fused' => $this->resource->fused,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'fused' => ['type' => ['boolean', 'null']],
            ],
            'required'             => [
                'fused',
            ],
            'additionalProperties' => false,
        ];
    }
}
