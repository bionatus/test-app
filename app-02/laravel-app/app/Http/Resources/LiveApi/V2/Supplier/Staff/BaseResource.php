<?php

namespace App\Http\Resources\LiveApi\V2\Supplier\Staff;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\StaffResource;
use App\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private StaffResource $baseResource;

    public function __construct(Staff $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new StaffResource($resource);
    }

    public function toArray($request)
    {
        $response               = $this->baseResource->toArray($request);

        return $response;
    }

    public static function jsonSchema(): array
    {
        return StaffResource::jsonSchema();
    }
}
