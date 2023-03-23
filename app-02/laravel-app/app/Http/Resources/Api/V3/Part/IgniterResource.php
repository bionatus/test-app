<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Igniter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\IgniterResource as IgniterResourceModel;

/**
 * @property Igniter $resource
 */
class IgniterResource extends JsonResource implements HasJsonSchema
{
    private IgniterResourceModel $igniterResource;

    public function __construct(Igniter $resource)
    {
        parent::__construct($resource);
        $this->igniterResource = new IgniterResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->igniterResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return IgniterResourceModel::jsonSchema();
    }
}
