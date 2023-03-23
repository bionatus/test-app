<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource;
use App\Models\Oem;
use App\Models\Tag;
use App\Models\Warning;
use App\Models\Warning\Scopes\ByTitles;
use App\Types\TaggableType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private OemResource $baseResource;

    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new OemResource($resource);
    }

    public function toArray($request)
    {
        $oem      = $this->resource;
        $tags     = TaggableType::query(Tag::TYPE_MODEL_TYPE, $oem->series->getRouteKey())->paginate(15);
        $warnings = Warning::scoped(new ByTitles(explode(',', $oem->warnings)))->get();

        $baseResource = $this->baseResource->toArray($request);

        return array_replace_recursive($baseResource, [
            'status'                  => $oem->status,
            'model_description'       => $oem->model_description,
            'series'                  => new SeriesResource($oem->series),
            'system_details'          => new SystemDetailsResource($oem),
            'refrigerant_details'     => new RefrigerantDetailsResource($oem),
            'compressor_details'      => new CompressorDetailsResource($oem),
            'oil_details'             => new OilDetailsResource($oem),
            'metering_device_details' => new MeteringDeviceDetailsResource($oem),
            'tags'                    => new TagCollection($tags),
            'manuals'                 => new ManualsResource($oem),
            'conversions'             => new ConversionJobsResource($oem),
            'warnings'                => new WarningCollection($warnings),
            'posts_count'             => $this->resource->postsCount(),
        ]);
    }

    public static function jsonSchema(): array
    {
        $baseSchema = OemResource::jsonSchema();

        return array_merge_recursive($baseSchema, [
            'properties' => [
                'status'                  => ['type' => ['string', 'null']],
                'model_description'       => ['type' => ['string', 'null']],
                'series'                  => SeriesResource::jsonSchema(),
                'system_details'          => SystemDetailsResource::jsonSchema(),
                'refrigerant_details'     => RefrigerantDetailsResource::jsonSchema(),
                'compressor_details'      => CompressorDetailsResource::jsonSchema(),
                'oil_details'             => OilDetailsResource::jsonSchema(),
                'metering_device_details' => MeteringDeviceDetailsResource::jsonSchema(),
                'tags'                    => TagCollection::jsonSchema(),
                'manuals'                 => ManualsResource::jsonSchema(),
                'conversions'             => ConversionJobsResource::jsonSchema(),
                'warnings'                => WarningCollection::jsonSchema(),
                'posts_count'             => ['type' => ['integer']],
            ],
            'required'   => [
                'status',
                'model_description',
                'series',
                'system_details',
                'refrigerant_details',
                'compressor_details',
                'oil_details',
                'metering_device_details',
                'tags',
                'manuals',
                'conversions',
                'warnings',
                'posts_count',
            ],
        ]);
    }
}
