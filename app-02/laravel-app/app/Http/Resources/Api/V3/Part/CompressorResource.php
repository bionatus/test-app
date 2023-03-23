<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\Compressor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\CompressorResource as CompressorResourceModel;

/**
 * @property Compressor $resource
 */
class CompressorResource extends JsonResource implements HasJsonSchema
{
    private CompressorResourceModel $compressorResource;

    public function __construct(Compressor $resource)
    {
        parent::__construct($resource);
        $this->compressorResource = new CompressorResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->compressorResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CompressorResourceModel::jsonSchema();
    }
}
