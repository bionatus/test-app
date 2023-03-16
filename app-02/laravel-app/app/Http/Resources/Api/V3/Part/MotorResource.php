<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Motor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\MotorResource as MotorResourceModel;

/**
 * @property Motor $resource
 */
class MotorResource extends JsonResource implements HasJsonSchema
{
    private MotorResourceModel $motorResource;

    public function __construct(Motor $resource)
    {
        parent::__construct($resource);
        $this->motorResource = new MotorResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->motorResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return MotorResourceModel::jsonSchema();
    }
}
