<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\Media;
use App\Models\Note;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Note $resource
 */
class NoteResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Note $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $note = $this->resource;
        /** @var Media $image */
        $image = $note->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'        => $note->getRouteKey(),
            'image'     => $image ? new ImageResource($image) : null,
            'title'     => $note->title,
            'body'      => $note->body,
            'link'      => $note->link,
            'link_text' => $note->link_text,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'        => ['type' => ['string']],
                'image'     => ImageResource::jsonSchema(true),
                'title'     => ['type' => ['string']],
                'body'      => ['type' => ['string']],
                'link'      => ['type' => ['string', 'null']],
                'link_text' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'image',
                'title',
                'body',
                'link',
                'link_text',
            ],
            'additionalProperties' => false,
        ];
    }
}
