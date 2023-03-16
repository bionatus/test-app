<?php

namespace App\Http\Resources\Api\V3\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\Company;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Company $resource
 */
class CompanyResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Company $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->resource->getRouteKey(),
            'name'       => $this->resource->name,
            'type'       => $this->resource->type,
            'country'    => $this->resource->country,
            'state'      => $this->resource->state,
            'city'       => $this->resource->city,
            'address'    => $this->resource->address,
            'zip_code'   => $this->resource->zip_code,
            'latitude'   => $this->resource->latitude,
            'longitude'  => $this->resource->longitude,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'name'       => ['type' => ['string', 'null']],
                'type  '     => ['type' => ['string', 'null']],
                'country'    => ['type' => ['string', 'null']],
                'state'      => ['type' => ['string', 'null']],
                'city'       => ['type' => ['string', 'null']],
                'address'    => ['type' => ['string', 'null']],
                'zip_code'   => ['type' => ['string', 'null']],
                'latitude'   => ['type' => ['string', 'null']],
                'longitude'  => ['type' => ['string', 'null']],
                'created_at' => ['type' => ['string']],
                'updated_at' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
                'type',
                'country',
                'state',
                'city',
                'address',
                'zip_code',
                'latitude',
                'longitude',
                'created_at',
                'updated_at',
            ],
            'additionalProperties' => true,
        ];
    }
}
