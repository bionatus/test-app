<?php

namespace App\Http\Resources\LiveApi\V1\Auth\Email\Login;

use App\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Staff $resource */
class BaseResource extends JsonResource
{
    private string $token;

    public function __construct(Staff $resource, string $token)
    {
        parent::__construct($resource);

        $this->token = $token;
    }

    public function toArray($request)
    {
        $staff = $this->resource;
        $token = $this->token;

        return [
            'id'                      => $staff->getRouteKey(),
            'initial_password_set_at' => $staff->initial_password_set_at,
            'token'                   => $token,
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
                'id'                      => ['type' => ['string']],
                'initial_password_set_at' => ['type' => ['string', 'null']],
                'token'                   => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'initial_password_set_at',
                'token',
            ],
            'additionalProperties' => true,
        ];
    }
}
