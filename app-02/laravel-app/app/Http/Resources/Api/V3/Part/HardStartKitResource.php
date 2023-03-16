<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\HardStartKit;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\HardStartKitResource as HardStartKitResourceModel;

/**
 * @property HardStartKit $resource
 */
class HardStartKitResource extends JsonResource implements HasJsonSchema
{
    private HardStartKitResourceModel $hardStartKitResource;

    public function __construct(HardStartKit $resource)
    {
        parent::__construct($resource);
        $this->hardStartKitResource = new HardStartKitResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->hardStartKitResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return HardStartKitResourceModel::jsonSchema();
    }
}
