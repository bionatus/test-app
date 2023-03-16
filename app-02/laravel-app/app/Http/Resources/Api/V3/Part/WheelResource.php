<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Wheel;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\WheelResource as WheelResourceModel;

/**
 * @property Wheel $resource
 */
class WheelResource extends JsonResource implements HasJsonSchema
{
    private WheelResourceModel $wheelResource;

    public function __construct(Wheel $resource)
    {
        parent::__construct($resource);
        $this->wheelResource = new WheelResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->wheelResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return WheelResourceModel::jsonSchema();
    }
}
