<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Technician;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Technician $resource
 */
class TechnicianResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Technician $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $technician = $this->resource;

        return [
            'id'                  => $technician->id,
            'name'                => $technician->name,
            'code'                => $technician->code,
            'phone'               => $technician->phone,
            'years_of_experience' => $technician->years_of_experience,
            'image'               => $technician->imageUrl(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties' => [
                'id'                  => ['type' => ['string']],
                'name'                => ['type' => ['string', 'null']],
                'code'                => ['type' => ['string', 'null']],
                'phone'               => ['type' => ['string', 'null']],
                'years_of_experience' => ['type' => ['integer', 'null']],
                'image'               => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'id',
            ],
        ];
    }
}
