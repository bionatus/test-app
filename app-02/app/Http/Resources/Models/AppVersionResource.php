<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\AppVersion;
use Illuminate\Http\Resources\Json\JsonResource;

class AppVersionResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AppVersion $appVersion)
    {
        parent::__construct($appVersion);
    }

    public function toArray($request): array
    {
        return [
            'min'         => $this->resource->min,
            'current'     => $this->resource->current,
            'video_title' => $this->resource->video_title,
            'video_url'   => $this->resource->video_url,
            'message'     => $this->resource->message,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'min'         => ['type' => ['string']],
                'current'     => ['type' => ['string']],
                'video_title' => ['type' => ['string', 'null']],
                'video_url'   => ['type' => ['string', 'null']],
                'message'     => ['type' => ['string']],
            ],
            'required'             => [
                'min',
                'current',
                'video_title',
                'video_url',
                'message',
            ],
            'additionalProperties' => false,
        ];
    }
}
