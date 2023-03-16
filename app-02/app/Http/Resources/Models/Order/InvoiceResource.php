<?php

namespace App\Http\Resources\Models\Order;

use App\Http\Resources\HasJsonSchema;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Media $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'  => $this->resource->uuid,
            'url' => $this->resource->getUrl(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'  => ['type' => ['string']],
                'url' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'url',
            ],
            'additionalProperties' => false,
        ];
    }
}
