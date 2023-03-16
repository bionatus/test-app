<?php

namespace App\Http\Resources\Api\V3\Store;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\StateResource as BaseStateResource;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\State;

/**
 * @property State $resource
 */
class StateResource extends JsonResource implements HasJsonSchema
{
    private BaseStateResource $baseResource;

    public function __construct(State $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new BaseStateResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseStateResource::jsonSchema();
    }
}
