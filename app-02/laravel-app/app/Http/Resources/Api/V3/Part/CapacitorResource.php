<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Capacitor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\CapacitorResource as CapacitorResourceModel;

/**
 * @property Capacitor $resource
 */
class CapacitorResource extends JsonResource implements HasJsonSchema
{
    private CapacitorResourceModel $capacitorResource;

    public function __construct(Capacitor $resource)
    {
        parent::__construct($resource);
        $this->capacitorResource = new CapacitorResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->capacitorResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CapacitorResourceModel::jsonSchema();
    }
}
