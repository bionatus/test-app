<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\SupportCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupportCall $resource
 */
class SupportCallResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupportCall $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $supportCall = $this->resource;

        $user            = $supportCall->user;
        $oem             = $supportCall->oem;
        $missingOemBrand = $supportCall->missingOemBrand;

        return [
            'id'                       => $supportCall->getRouteKey(),
            'category'                 => $supportCall->category,
            'subcategory'              => $supportCall->subcategory,
            'user'                     => $user ? new UserResource($user) : null,
            'oem'                      => $oem ? new OemResource($oem) : null,
            'missing_oem_brand'        => $missingOemBrand ? new BrandResource($missingOemBrand) : null,
            'missing_oem_model_number' => $supportCall->missing_oem_model_number,
            'created_at'               => $supportCall->created_at,
        ];
    }

    public static function jsonSchema(): array
    {
        $oemResource             = OemResource::jsonSchema();
        $userResource            = UserResource::jsonSchema();
        $brandResource           = BrandResource::jsonSchema();
        $oemResource['type'][]   = 'null';
        $userResource['type'][]  = 'null';
        $brandResource['type'][] = 'null';

        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                       => ['type' => ['string']],
                'category'                 => ['type' => ['string']],
                'subcategory'              => ['type' => ['string', 'null']],
                'user'                     => $userResource,
                'oem'                      => $oemResource,
                'missing_oem_brand'        => $brandResource,
                'missing_oem_model_number' => ['type' => ['string', 'null']],
                'created_at'               => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'category',
                'subcategory',
                'user',
                'oem',
                'missing_oem_brand',
                'missing_oem_model_number',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
