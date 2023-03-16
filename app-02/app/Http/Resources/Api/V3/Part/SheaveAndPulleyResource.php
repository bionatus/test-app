<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\SheaveAndPulley;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\SheaveAndPulleyResource as SheaveAndPulleyResourceModel;

/**
 * @property SheaveAndPulley $resource
 */
class SheaveAndPulleyResource extends JsonResource implements HasJsonSchema
{
    private SheaveAndPulleyResourceModel $sheaveAndPulleyResource;

    public function __construct(SheaveAndPulley $resource)
    {
        parent::__construct($resource);
        $this->sheaveAndPulleyResource = new SheaveAndPulleyResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->sheaveAndPulleyResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SheaveAndPulleyResourceModel::jsonSchema();
    }
}
