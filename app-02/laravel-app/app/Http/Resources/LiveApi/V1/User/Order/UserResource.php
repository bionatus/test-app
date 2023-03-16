<?php

namespace App\Http\Resources\LiveApi\V1\User\Order;

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
        return array_merge_recursive($this->userResource->toArray($request), [
            'company' => $this->resource->companyName(),
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema                          = BaseUserResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'company' => ['type' => ['string', 'null']],
            ],
            'required' => [
                'company',
            ]
        ]);
    }
}
