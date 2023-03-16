<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\FanBlade;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\FanBladeResource as FanBladeResourceModel;

/**
 * @property FanBlade $resource
 */
class FanBladeResource extends JsonResource implements HasJsonSchema
{
    private FanBladeResourceModel $fanBladeResource;

    public function __construct(FanBlade $resource)
    {
        parent::__construct($resource);
        $this->fanBladeResource = new FanBladeResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->fanBladeResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return FanBladeResourceModel::jsonSchema();
    }
}
