<?php

namespace App\Http\Resources\Api\V3\Auth;

use App\Http\Resources\HasJsonSchema;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property User $resource
 */
abstract class UserResource extends JsonResource implements HasJsonSchema
{
    private string $token;

    public function __construct(User $resource, string $token)
    {
        parent::__construct($resource);

        $this->token = $token;
    }

    public function toArray($request)
    {
        $user  = $this->resource;
        $token = $this->token;

        return [
            'id'                     => $user->getRouteKey(),
            'first_name'             => $user->first_name,
            'last_name'              => $user->last_name,
            'accredited'             => $user->isAccredited(),
            'registration_completed' => $user->isRegistered(),
            'photo'                  => $user->photo ? Storage::url($user->photo) : null,
            'tos_accepted'           => $user->hasToSAccepted(),
            'notifications_count'    => $user->getUnreadNotificationsCount(),
            'verified'               => $user->isVerified(),
            'manual_download_count'  => $user->manual_download_count,
            'token'                  => $token,
        ];
    }

    public function toArrayWithAdditionalData(array $data = []): array
    {
        return array_merge($this->resolve(), $data);
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                     => ['type' => ['integer']],
                'first_name'             => ['type' => ['string']],
                'last_name'              => ['type' => ['string']],
                'accredited'             => ['type' => ['boolean']],
                'registration_completed' => ['type' => ['boolean']],
                'photo'                  => ['type' => ['string', 'null']],
                'tos_accepted'           => ['type' => ['boolean']],
                'notifications_count'    => ['type' => ['integer']],
                'verified'               => ['type' => ['boolean']],
                'manual_download_count'  => ['type' => ['integer']],
                'token'                  => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'first_name',
                'last_name',
                'accredited',
                'registration_completed',
                'photo',
                'tos_accepted',
                'notifications_count',
                'verified',
                'manual_download_count',
                'token',
            ],
            'additionalProperties' => true,
        ];
    }
}
