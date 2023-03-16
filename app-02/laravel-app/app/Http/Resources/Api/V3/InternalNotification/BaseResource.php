<?php

namespace App\Http\Resources\Api\V3\InternalNotification;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\InternalNotificationResource;
use App\Models\InternalNotification;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property InternalNotification $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private InternalNotificationResource $baseResource;

    public function __construct(InternalNotification $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new InternalNotificationResource($resource);
    }

    public function toArray($request)
    {
        $resource         = $this->baseResource->toArray($request);
        $resource['user'] = new UserResource($this->resource->user);

        return $resource;
    }

    public static function jsonSchema(): array
    {
        $schema                       = InternalNotificationResource::jsonSchema();
        $schema['properties']['user'] = UserResource::jsonSchema();

        return $schema;
    }
}
