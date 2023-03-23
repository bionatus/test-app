<?php

namespace App\Http\Resources\LiveApi\V1\InternalNotification\Supplier;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\InternalNotificationResource;
use App\Models\InternalNotification;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property InternalNotification $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private InternalNotificationResource $internalNotificationResource;

    public function __construct(InternalNotification $resource)
    {
        parent::__construct($resource);

        $this->internalNotificationResource = new InternalNotificationResource($resource);
    }

    public function toArray($request)
    {
        $response = $this->internalNotificationResource->toArray($request);

        return array_merge($response, [
            'user' => new UserResource($this->resource->user),
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema                       = InternalNotificationResource::jsonSchema();
        $schema['properties']['user'] = UserResource::jsonSchema();

        return $schema;
    }
}