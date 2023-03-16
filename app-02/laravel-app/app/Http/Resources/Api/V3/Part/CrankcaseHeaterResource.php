<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\CrankcaseHeater;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\CrankcaseHeaterResource as CrankcaseHeaterResourceModel;

/**
 * @property CrankcaseHeater $resource
 */
class CrankcaseHeaterResource extends JsonResource implements HasJsonSchema
{
    private CrankcaseHeaterResourceModel $crankcaseHeaterResource;

    public function __construct(CrankcaseHeater $resource)
    {
        parent::__construct($resource);
        $this->crankcaseHeaterResource = new CrankcaseHeaterResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->crankcaseHeaterResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CrankcaseHeaterResourceModel::jsonSchema();
    }
}
