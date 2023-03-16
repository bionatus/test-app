<?php

namespace App\Http\Resources\LiveApi\V1\Address\Country;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\CountryResource;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private CountryResource $baseResource;

    public function __construct(Country $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new CountryResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CountryResource::jsonSchema();
    }
}
