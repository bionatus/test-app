<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\FilterDrierAndCore;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\FilterDrierAndCoreResource as FilterDrierAndCoreResourceModel;

/**
 * @property FilterDrierAndCore $resource
 */
class FilterDrierAndCoreResource extends JsonResource implements HasJsonSchema
{
    private FilterDrierAndCoreResourceModel $filterDrierAndCoreResource;

    public function __construct(FilterDrierAndCore $resource)
    {
        parent::__construct($resource);
        $this->filterDrierAndCoreResource = new FilterDrierAndCoreResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->filterDrierAndCoreResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return FilterDrierAndCoreResourceModel::jsonSchema();
    }
}
