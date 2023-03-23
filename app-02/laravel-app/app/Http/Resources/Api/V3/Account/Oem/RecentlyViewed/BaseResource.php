<?php

namespace App\Http\Resources\Api\V3\Account\Oem\RecentlyViewed;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OemResource $oemResource;

    public function __construct(Oem $resource)
    {
        parent::__construct($resource);

        $this->oemResource = new OemResource($resource);
    }

    public function toArray($request)
    {
        $oemResource = $this->oemResource->toArray($request);

        $oemResource['visited_at'] = $this->oemResource->visited_at;

        return $oemResource;
    }

    public static function jsonSchema(): array
    {
        $schema                             = OemResource::jsonSchema();
        $schema['properties']['visited_at'] = ['type' => ['string']];
        $schema['required'][]               = 'visited_at';

        return $schema;
    }
}
