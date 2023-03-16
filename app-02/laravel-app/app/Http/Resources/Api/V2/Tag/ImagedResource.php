<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggableType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TaggableType $resource
 */
class ImagedResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TaggableType $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $resource = new BaseResource($this->resource);

        $isSeries = Tag::TYPE_SERIES === $this->resource->type;
        if ($isSeries) {
            /** @var Series $series */
            $series = $this->resource->taggable();

            return $resource->toArrayWithAdditionalData([
                'images' => new SeriesImageCollection($series && $series->image ? [$series->image] : []),
            ]);
        }

        return $resource->toArrayWithAdditionalData([
            'images' => new ImageCollection($this->resource->getMedia()),
        ]);
    }

    public function toArrayWithAdditionalData(array $data = []): array
    {
        return array_merge($this->resolve(), $data);
    }

    public static function jsonSchema(): array
    {
        $baseResourceSchema = BaseResource::jsonSchema();

        return array_merge_recursive($baseResourceSchema, [
            'properties' => [
                'images' => ImageCollection::jsonSchema(),
            ],
            'required'   => [
                'images',
            ],
        ]);
    }
}
