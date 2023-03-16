<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Part\ImageResource;
use App\Models\Other;
use App\Models\Part;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Part $resource
 */
class PartResource extends JsonResource implements HasJsonSchema
{
    private bool $hideNumber;

    public function __construct(Part $resource, bool $hideNumber = false)
    {
        parent::__construct($resource);

        $this->hideNumber = $hideNumber;
    }

    public function toArray($request): array
    {
        $part  = $this->resource;
        $image = $part->image;
        /** @var Other|null $otherDescription */
        $otherDescription = ($part->isOther() && $part->detail) ? $part->detail->description : null;

        return [
            'id'          => $part->item->getRouteKey(),
            'number'      => ($this->hideNumber) ? $part->hiddenNumber() : $part->number,
            'type'        => $part->type,
            'subtype'     => $part->subtype,
            'description' => $otherDescription,
            'brand'       => $part->brand,
            'image'       => $image ? new ImageResource($image) : null,
            'subcategory' => $part->subcategory,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'number'      => ['type' => ['string']],
                'type'        => ['type' => ['string']],
                'subtype'     => ['type' => ['string', 'null']],
                'description' => ['type' => ['string', 'null']],
                'brand'       => ['type' => ['string', 'null']],
                'image'       => ImageResource::jsonSchema(),
                'subcategory' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'number',
                'type',
                'subtype',
                'description',
                'brand',
                'image',
                'subcategory',
            ],
            'additionalProperties' => false,
        ];
    }
}
