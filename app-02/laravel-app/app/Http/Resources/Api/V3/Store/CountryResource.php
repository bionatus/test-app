<?php

namespace App\Http\Resources\Api\V3\Store;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\CountryResource as BaseCountryResource;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;

/**
 * @property Country $resource
 */
class CountryResource extends JsonResource implements HasJsonSchema
{
    private BaseCountryResource $baseResource;

    public function __construct(Country $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseCountryResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseCountryResource::jsonSchema();
    }
}
