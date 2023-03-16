<?php

namespace App\Http\Resources\BasecampApi\V1\SupportCall;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupportCallResource;
use App\Models\SupportCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupportCall $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupportCallResource $baseResource;

    public function __construct(SupportCall $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new SupportCallResource($resource);
    }

    public function toArray($request)
    {
        $resource = $this->baseResource;
        $oem      = $this->resource->oem;

        return array_replace_recursive($resource->toArray($request), [
            'user' => new UserResource($this->resource->user),
            'oem'  => $oem ? new OemResource($this->resource->oem) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema                       = SupportCallResource::jsonSchema();
        $schema['properties']['user'] = UserResource::jsonSchema();
        $schema['properties']['oem']  = OemResource::jsonSchema();
        $schema['required'][]         = 'user';
        $schema['required'][]         = 'oem';

        return $schema;
    }
}
