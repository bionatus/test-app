<?php

namespace App\Http\Resources\Api\V3\Point\XoxoVoucher;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\XoxoVoucherResource;
use App\Models\XoxoVoucher;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property XoxoVoucher $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private XoxoVoucherResource $baseResource;

    public function __construct(XoxoVoucher $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new XoxoVoucherResource($resource);
    }

    public function toArray($request)
    {
        $resource                       = $this->baseResource->toArray($request);
        $resource['first_denomination'] = $this->resource->first_denomination;

        return $resource;
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(XoxoVoucherResource::jsonSchema(), [
            'properties' => [
                'first_denomination' => ['type' => ['integer']],
            ],
            'required'   => [
                'first_denomination',
            ],
        ]);
    }
}
