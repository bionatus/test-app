<?php

namespace App\Http\Resources\LiveApi\V1\Order\Unprocessed;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Order\UserResource as UserBaseResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    private UserBaseResource $userBaseResource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);

        $this->userBaseResource = new UserBaseResource($resource);
    }

    public function toArray($request)
    {
        $oldestPendingOrder = $this->resource->oldestPendingOrder;

        $response                                    = $this->userBaseResource->toArray($request);
        $response['oldest_pending_order_created_at'] = $oldestPendingOrder ? $oldestPendingOrder->created_at : null;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = UserBaseResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'oldest_pending_order_created_at' => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'oldest_pending_order_created_at',
            ],
        ]);
    }
}
