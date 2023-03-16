<?php

namespace App\Http\Resources\LiveApi\V2\Supplier\User;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property User $resource */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $user                  = $this->resource;
        $hasMedia              = $user->hasMedia(MediaCollectionNames::IMAGES);
        $hasPhoto              = $user->photoUrl();
        $pubnubChannel         = $user->pubnubChannels->first();
        $supplierUser          = $user->supplierUsers->first();
        $device                = $user->devices->first();
        $oldestPendingOrder    = $user->orders->first();
        $pushNotificationToken = $device ? $device->pushNotificationToken : null;

        return [
            'id'                              => $user->getKey(),
            'name'                            => $user->fullName(),
            'company'                         => $user->companyName(),
            'image'                           => ($hasMedia || $hasPhoto) ? new ImageResource($user) : null,
            'chat'                            => new ChatResource($pubnubChannel),
            'push_notification_token'         => $pushNotificationToken ? $pushNotificationToken->token : null,
            'supplier_user'                   => $supplierUser ? new SupplierUserResource($supplierUser) : null,
            'oldest_pending_order_created_at' => $oldestPendingOrder ? $oldestPendingOrder->created_at : null,
            'has_working_orders'              => $user->orders_exists,
            'pending_orders_count'            => (int) $user->pending_orders_count,
            'pending_approval_orders_count'   => (int) $user->pending_approval_orders_count,
        ];
    }

    public static function jsonSchema(): array
    {
        $supplierUserResourceSchema           = SupplierUserResource::jsonSchema();
        $supplierUserResourceSchema['type'][] = 'null';

        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                              => ['type' => ['integer']],
                'name'                            => ['type' => ['string']],
                'company'                         => ['type' => ['string', 'null']],
                'image'                           => ImageResource::jsonSchema(),
                'chat'                            => ChatResource::jsonSchema(),
                'push_notification_token'         => ['type' => ['string', 'null']],
                'supplier_user'                   => $supplierUserResourceSchema,
                'oldest_pending_order_created_at' => ['type' => ['string', 'null']],
                'has_working_orders'              => ['type' => ['boolean']],
                'pending_orders_count'            => ['type' => ['integer']],
                'pending_approval_orders_count'   => ['type' => ['integer']],
            ],
            'required'             => [
                'id',
                'name',
                'company',
                'image',
                'chat',
                'push_notification_token',
                'supplier_user',
                'oldest_pending_order_created_at',
                'has_working_orders',
                'pending_orders_count',
                'pending_approval_orders_count',
            ],
            'additionalProperties' => false,
        ];
    }
}
