<?php

namespace App\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\HasJsonSchema;
use App\Models\Topic;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Topic $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Topic $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $topic   = $this->resource;
        $subject = $this->resource->subject;

        return [
            'id'          => $subject->getRouteKey(),
            'name'        => $subject->name,
            'description' => $topic->description,
            'subtopics'   => new SubtopicCollection($topic->subtopics),
            'images'      => new ImageCollection($subject->getMedia(MediaCollectionNames::IMAGES)),
            'tools'       => new ToolCollection($subject->tools),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'name'        => ['type' => ['string']],
                'description' => ['type' => ['string']],
                'subtopics'   => SubtopicCollection::jsonSchema(),
                'images'      => ImageCollection::jsonSchema(),
                'tools'       => ToolCollection::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'description',
                'subtopics',
                'images',
                'tools',
            ],
            'additionalProperties' => false,
        ];
    }
}
