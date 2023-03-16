<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\ControlBoard;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\ControlBoardResource as ControlBoardResourceModel;

/**
 * @property ControlBoard $resource
 */
class ControlBoardResource extends JsonResource implements HasJsonSchema
{
    private ControlBoardResourceModel $controlBoardResource;

    public function __construct(ControlBoard $resource)
    {
        parent::__construct($resource);
        $this->controlBoardResource = new ControlBoardResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->controlBoardResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return ControlBoardResourceModel::jsonSchema();
    }
}
