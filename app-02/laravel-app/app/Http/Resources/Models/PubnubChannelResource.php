<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\PubnubChannel;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PubnubChannel $resource
 */
class PubnubChannelResource extends JsonResource implements HasJsonSchema
{
    public function __construct(PubnubChannel $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $pubnubChannel = $this->resource;

        return [
            'id'       => $pubnubChannel->getKey(),
            'channel'  => $pubnubChannel->getRouteKey(),
            'supplier' => new SupplierResource($pubnubChannel->supplier),
            'user'     => new UserResource($pubnubChannel->user),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'       => ['type' => ['integer']],
                'channel'  => ['type' => ['string']],
                'supplier' => SupplierResource::jsonSchema(),
                'user'     => UserResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'channel',
                'supplier',
                'user',
            ],
            'additionalProperties' => false,
        ];
    }
}
