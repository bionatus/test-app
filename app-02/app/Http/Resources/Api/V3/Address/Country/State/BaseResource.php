<?php

namespace App\Http\Resources\Api\V3\Address\Country\State;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\StateResource;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\State;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private StateResource $baseResource;

    public function __construct(State $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new StateResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return StateResource::jsonSchema();
    }
}
