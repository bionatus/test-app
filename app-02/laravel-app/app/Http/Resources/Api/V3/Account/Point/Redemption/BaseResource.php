<?php

namespace App\Http\Resources\Api\V3\Account\Point\Redemption;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\XoxoRedemptionResource;
use App\Models\XoxoRedemption;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property XoxoRedemption $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private XoxoRedemptionResource $baseResource;

    public function __construct(XoxoRedemption $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new XoxoRedemptionResource($resource);
    }

    public function toArray($request): array
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return XoxoRedemptionResource::jsonSchema();
    }
}
