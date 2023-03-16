<?php

namespace App\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\HasJsonSchema;
use App\Models\Subtopic;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Subtopic $resource
 */
class SubtopicResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Subtopic $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $subject = $this->resource->subject;

        return [
            'id'     => $subject->getRouteKey(),
            'name'   => $subject->name,
            'images' => new ImageCollection($subject->getMedia(MediaCollectionNames::IMAGES)),
            'tools'  => new ToolCollection($subject->tools),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'     => ['type' => ['string']],
                'name'   => ['type' => ['string']],
                'images' => ImageCollection::jsonSchema(),
                'tools'  => ToolCollection::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'images',
            ],
            'additionalProperties' => false,
        ];
    }
}
