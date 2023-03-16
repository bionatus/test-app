<?php

namespace App\Http\Resources\Api\V3\Account\VerifiedSupplier;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(int $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'has_verified_suppliers' => !!$this->resource,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'has_verified_suppliers' => ['type' => ['boolean']],
            ],
            'required'             => [
                'has_verified_suppliers',
            ],
            'additionalProperties' => false,
        ];
    }
}
