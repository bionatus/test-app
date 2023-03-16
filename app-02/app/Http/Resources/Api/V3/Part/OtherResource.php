<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Other;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\OtherResource as OtherResourceModel;

/**
 * @property Other $resource
 */
class OtherResource extends JsonResource implements HasJsonSchema
{
    private OtherResourceModel $otherResource;

    public function __construct(Other $resource)
    {
        parent::__construct($resource);
        $this->otherResource = new OtherResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->otherResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return OtherResourceModel::jsonSchema();
    }
}
