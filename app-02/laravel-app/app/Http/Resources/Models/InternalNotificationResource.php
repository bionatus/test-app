<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\InternalNotification;
use App\Types\LinkResourceType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property InternalNotification $resource
 */
class InternalNotificationResource extends JsonResource implements HasJsonSchema
{
    public function __construct(InternalNotification $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $internalNotification = $this->resource;
        $source               = new LinkResourceType($internalNotification->source_event,
            $internalNotification->source_type, $internalNotification->source_id, $internalNotification->data);

        return [
            'id'         => $internalNotification->getRouteKey(),
            'message'    => $internalNotification->message,
            'created_at' => $internalNotification->created_at,
            'read_at'    => $internalNotification->read_at,
            'user'       => new UserResource($internalNotification->user),
            'source'     => $source->toArray(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'message'    => ['type' => ['string']],
                'created_at' => ['type' => ['string']],
                'read_at'    => ['type' => ['string', 'null']],
                'user'       => UserResource::jsonSchema(),
                'source'     => LinkResourceType::jsonSchema(),
            ],
            'required'             => [
                'id',
                'message',
                'created_at',
                'read_at',
                'user',
                'source',
            ],
            'additionalProperties' => false,
        ];
    }
}
