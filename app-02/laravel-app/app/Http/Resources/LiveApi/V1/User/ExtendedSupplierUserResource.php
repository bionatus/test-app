<?php

namespace App\Http\Resources\LiveApi\V1\User;

use App\Models\SupplierUser;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\SupplierUserResource as BaseSupplierUserResource;

/**
 * @property SupplierUser $resource
 */
class ExtendedSupplierUserResource extends JsonResource
{
    private BaseSupplierUserResource $baseSupplierUserResource;

    public function __construct(SupplierUser $resource)
    {
        parent::__construct($resource);
        $this->baseSupplierUserResource = new BaseSupplierUserResource($resource);
    }

    public function toArray($request): array
    {
        $user        = $this->resource->user;
        $photo       = $user->photoUrl();
        $companyUser = $user->companyUser;

        return array_replace_recursive($this->baseSupplierUserResource->toArray($request), [
            'id'            => $user->getRouteKey(),
            'name'          => $user->fullName(),
            'company'       => $companyUser ? $companyUser->company->name : null,
            'photo'         => $photo ? new ImageResource($photo) : null,
            'zip'           => $companyUser ? $companyUser->company->zip_code : null,
            'created_at'    => $this->resource->created_at,
        ]);
    }

    public static function jsonSchema(): array
    {
        $imageResourceSchema         = ImageResource::jsonSchema();
        $imageResourceSchema['type'] = ['object', 'array', 'null'];

        return array_replace_recursive(BaseSupplierUserResource::jsonSchema(), [
            'properties'           => [
                'id'            => ['type' => ['string', 'integer']],
                'name'          => ['type' => ['string']],
                'company'       => ['type' => ['null', 'string']],
                'photo'         => $imageResourceSchema,
                'zip'           => ['type' => ['null', 'string']],
                'created_at'    => ['type' => ['string']],

            ],
            'required'             => [
                'id',
                'name',
                'company',
                'photo',
                'zip',
                'created_at',
            ],
        ]);
    }
}
