<?php

namespace App\Http\Resources\LiveApi\V1\InternalNotification\Supplier;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\UserResource as ModelsUserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    private ModelsUserResource $userResource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);

        $this->userResource = new ModelsUserResource($resource);
    }

    public function toArray($request): array
    {
        $user = $this->resource;

        return array_merge_recursive($this->userResource->toArray($request), [
            'name'       => $user->fullName(),
            'company'    => $user->companyName(),
            'experience' => $user->experience_years,
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema = ModelsUserResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'name'       => ['type' => ['string', 'null']],
                'company'    => ['type' => ['string', 'null']],
                'experience' => ['type' => ['integer', 'null']],
            ],
            'required'   => [
                'name',
                'company',
                'experience',
            ],
        ]);
    }
}
