<?php

namespace App\Http\Resources\Api\V3\Technician;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\TechnicianResource;
use App\Models\Technician;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Technician $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private TechnicianResource $technicianResource;

    public function __construct(Technician $resource)
    {
        parent::__construct($resource);
        $this->technicianResource = new TechnicianResource($resource);
    }

    public function toArray($request)
    {
        return $this->technicianResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return TechnicianResource::jsonSchema();
    }
}
