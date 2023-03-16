<?php

namespace App\Http\Resources\LiveApi\V1\User;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property */
class BaseResource extends JsonResource
{
    private Collection $confirmedUsers;
    private Collection $unconfirmedUsers;

    public function __construct(Collection $confirmedUsers, Collection $unconfirmedUsers)
    {
        parent::__construct($confirmedUsers);
        $this->confirmedUsers   = $confirmedUsers;
        $this->unconfirmedUsers = $unconfirmedUsers;
    }

    public function toArray($request)
    {
        return [
            'unconfirmedUsers' => ExtendedSupplierUserResource::collection($this->unconfirmedUsers),
            'confirmedUsers'   => ExtendedSupplierUserResource::collection($this->confirmedUsers),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'unconfirmedUsers' => ['type' => ['array']],
                'confirmedUsers'   => ['type' => ['array']],
            ],
            'required'             => [
                'unconfirmedUsers',
                'confirmedUsers',
            ],
            'additionalProperties' => false,
        ];
    }
}
