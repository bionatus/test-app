<?php

namespace App\Http\Resources\Models;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

/**
 * @property User $resource
 */
class UserResource extends JsonResource
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $user  = $this->resource;
        $photo = $user->photo;

        return [
            'id'          => $user->getRouteKey(),
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'public_name' => $user->public_name,
            'photo'       => $photo ? Storage::url($photo) : null,
            'disabled'    => $user->isDisabled(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['integer']],
                'first_name'  => ['type' => ['string']],
                'last_name'   => ['type' => ['string']],
                'public_name' => ['type' => ['string', 'null']],
                'photo'       => ['type' => ['string', 'null']],
                'disabled'    => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'first_name',
                'last_name',
                'public_name',
                'photo',
                'disabled',
            ],
            'additionalProperties' => false,
        ];
    }
}
