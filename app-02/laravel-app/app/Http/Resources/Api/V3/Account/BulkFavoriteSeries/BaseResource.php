<?php

namespace App\Http\Resources\Api\V3\Account\BulkFavoriteSeries;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SeriesResource;
use App\Models\Series;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Series $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SeriesResource $seriesResource;

    public function __construct(Series $resource)
    {
        $this->seriesResource = new SeriesResource($resource);
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->seriesResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SeriesResource::jsonSchema();
    }
}
