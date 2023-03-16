<?php

namespace App\Http\Resources\LiveApi\V2\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\Part\ImageResource as PartImageResource;
use App\Models\Item;
use App\Models\Part;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class ItemResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Item $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $item = $this->resource;
        /** @var Part|Supply $orderable */
        $orderable = $item->orderable;

        $response = [
            'id'    => $item->getRouteKey(),
            'type'  => $item->type,
            'image' => null,
        ];

        if ($item->isPart()) {
            $image             = $orderable->image;
            $response['image'] = $image ? new PartImageResource($image) : null;
        }

        if ($item->isSupply()) {
            $media             = $orderable->getCategoryMedia();
            $response['image'] = $media ? new ImageResource($media) : null;
        }

        return $response;
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['array', 'object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'type'  => ['type' => ['string']],
                'image' => ['anyOf' => [ImageResource::jsonSchema(true), PartImageResource::jsonSchema()]],
            ],
            'required'             => [
                'id',
                'type',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
