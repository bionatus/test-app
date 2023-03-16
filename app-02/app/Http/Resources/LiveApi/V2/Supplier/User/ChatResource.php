<?php

namespace App\Http\Resources\LiveApi\V2\Supplier\User;

use App\Models\PubnubChannel;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PubnubChannel $resource
 */
class ChatResource extends JsonResource
{
    public function __construct(PubnubChannel $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $lastMessageAt = $this->resource->last_message_at;

        return [
            'channel'         => $this->resource->getRouteKey(),
            'last_message_at' => !empty($lastMessageAt) ? $lastMessageAt : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'channel'         => ['type' => ['string']],
                'last_message_at' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'channel',
                'last_message_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
