<?php

namespace App\Http\Resources\Api\V3\AppVersion;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AppVersionResource;
use App\Models\AppVersion;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private AppVersionResource $baseResource;
    private string             $clientVersion;

    public function __construct(AppVersion $resource, string $clientVersion)
    {
        parent::__construct($resource);

        $this->clientVersion = $clientVersion;

        $this->baseResource = new AppVersionResource($resource);
    }

    public function toArray($request)
    {
        $needsUpdate = $this->resource->needsUpdate($this->clientVersion);
        $resource    = $this->baseResource->toArray($request);

        $resource['requires_update'] = $needsUpdate;

        return $resource;
    }

    public static function jsonSchema(): array
    {
        $modelResourceSchema = AppVersionResource::jsonSchema();

        return array_merge_recursive($modelResourceSchema, [
            'properties' => [
                'requires_update' => ['type' => ['boolean']],
            ],
            'required'   => [
                'requires_update',
            ],
        ]);
    }
}
