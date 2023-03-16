<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\ConversionJob;
use App\Models\ConversionJob\Scopes\ByControls;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class ConversionJobsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $standardControls = ConversionJob::scoped(new ByControls(explode(',', $this->resource->standard_controls)))
            ->get();
        $optionalControls = ConversionJob::scoped(new ByControls(explode(',', $this->resource->optional_controls)))
            ->get();

        return [
            'standard_controls' => new ConversionJobCollection($standardControls),
            'optional_controls' => new ConversionJobCollection($optionalControls),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'standard_controls' => ConversionJobCollection::jsonSchema(),
                'optional_controls' => ConversionJobCollection::jsonSchema(),
            ],
            'required'             => [
                'standard_controls',
                'optional_controls',
            ],
            'additionalProperties' => false,
        ];
    }
}
