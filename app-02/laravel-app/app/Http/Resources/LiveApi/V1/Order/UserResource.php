<?php

namespace App\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\UserResource as BaseUserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    private BaseUserResource $userResource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);

        $this->userResource = new BaseUserResource($resource);
    }

    public function toArray($request)
    {
        $user                  = $this->resource;
        $device                = $user->devices->first();
        $pushNotificationToken = $device ? $device->pushNotificationToken : null;
        $data                  = $this->userResource->toArray($request);
        $data['photo']         = $user->photoUrl();

        return array_merge_recursive($data, [
            'company'                 => $user->companyName(),
            'push_notification_token' => $pushNotificationToken ? $pushNotificationToken->token : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema = BaseUserResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'company'                 => ['type' => ['string', 'null']],
                'push_notification_token' => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'company',
                'push_notification_token',
            ],
        ]);
    }
}
