<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Belt;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\BeltResource as BeltResourceModel;

/**
 * @property Belt $resource
 */
class BeltResource extends JsonResource implements HasJsonSchema
{
    private BeltResourceModel $beltResource;

    public function __construct(Belt $resource)
    {
        parent::__construct($resource);
        $this->beltResource = new BeltResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->beltResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BeltResourceModel::jsonSchema();
    }
}
