<?php

namespace App\Http\Resources\Models;

use App\Models\OrderLockedData;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OrderLockedData $resource
 */
class UserDeletedResource extends JsonResource
{
    public function __construct(OrderLockedData $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $lockedData = $this->resource;

        return [
            'id'          => null,
            'first_name'  => $lockedData->user_first_name,
            'last_name'   => $lockedData->user_last_name,
            'public_name' => $lockedData->user_public_name,
            'photo'       => null,
            'company'     => $lockedData->user_company,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'          => ['type' => ['null']],
                'first_name'  => ['type' => ['string']],
                'last_name'   => ['type' => ['string']],
                'public_name' => ['type' => ['string']],
                'photo'       => ['type' => ['null']],
                'company'     => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'first_name',
                'last_name',
                'public_name',
                'photo',
                'company',
            ],
            'additionalProperties' => false,
        ];
    }
}
