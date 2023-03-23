<?php

namespace App\Http\Resources\Api\V4\Account\PubnubChannel;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PubnubChannelResource as BasePubnubChannelResource;
use App\Models\PubnubChannel;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private BasePubnubChannelResource $baseResource;

    public function __construct(PubnubChannel $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new BasePubnubChannelResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BasePubnubChannelResource::jsonSchema();
    }
}
