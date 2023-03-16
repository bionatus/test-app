<?php

namespace App\Http\Resources\LiveApi\V2\Supplier\User;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource implements HasJsonSchema
{
    private array   $conversions = [];
    private ?string $id          = null;
    private ?string $url         = null;

    public function __construct(User $resource)
    {
        parent::__construct($resource);

        if ($media = $resource->getFirstMedia(MediaCollectionNames::IMAGES)) {
            $this->id          = $media->uuid;
            $this->url         = $media->getUrl();
            $this->conversions = [
                'thumb' => $this->when($media->hasGeneratedConversion(MediaConversionNames::THUMB),
                    fn() => $media->getUrl(MediaConversionNames::THUMB)),
            ];

            return;
        }

        if ($photoUrl = $resource->photoUrl()) {
            $this->url = $photoUrl;
        }
    }

    public function toArray($request): ?array
    {
        return [
            'id'          => $this->id,
            'url'         => $this->url,
            'conversions' => $this->conversions,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'          => ['type' => ['string', 'null']],
                'url'         => ['type' => ['string']],
                'conversions' => [
                    'type'  => ['array', 'object'],
                    'items' => [
                        'properties' => [
                            'thumb' => ['type' => ['string']],
                        ],
                    ],
                ],
            ],
            'required'             => [
                'id',
                'url',
                'conversions',
            ],
            'additionalProperties' => false,
        ];
    }
}
