<?php

namespace App\Http\Resources\Api\V3\User\Count;

use App;
use App\Http\Resources\HasJsonSchema;
use Coduo\PHPHumanizer\NumberHumanizer;
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
            'users_count' => NumberHumanizer::metricSuffix($this->resource),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'users_count' => ['type' => ['string']],
            ],
            'required'             => [
                'users_count',
            ],
            'additionalProperties' => false,
        ];
    }
}
