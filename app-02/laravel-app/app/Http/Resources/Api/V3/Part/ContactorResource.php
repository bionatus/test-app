<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Contactor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\ContactorResource as ContactorResourceModel;

/**
 * @property Contactor $resource
 */
class ContactorResource extends JsonResource implements HasJsonSchema
{
    private ContactorResourceModel $contactorResource;

    public function __construct(Contactor $resource)
    {
        parent::__construct($resource);
        $this->contactorResource = new ContactorResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->contactorResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return ContactorResourceModel::jsonSchema();
    }
}
