<?php

namespace App\Http\Resources\LiveApi\V1\Order\Unprocessed;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Order\BaseResource as OrderBaseResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderBaseResource $orderBaseResource;

    public function __construct(Order $resource)
    {
        parent::__construct($resource);

        $this->orderBaseResource = new OrderBaseResource($resource);
    }

    public function toArray($request)
    {
        $order            = $this->resource;
        $user             = $order->user;
        $response         = $this->orderBaseResource->toArray($request);
        $response['user'] = new UserResource($user);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                       = OrderBaseResource::jsonSchema();
        $schema['properties']['user'] = UserResource::jsonSchema();

        return $schema;
    }
}
