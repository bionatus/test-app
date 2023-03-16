<?php

namespace App\Http\Resources\Api\V4\Account\Company;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function toArray($request): array
    {
        $user            = $this->resource;
        $companyUser     = $user->companyUser;
        $jobTitle        = $companyUser->job_title;
        $equipmentType   = $companyUser->equipment_type ?? null;
        $companyResource = new CompanyResource($companyUser->company);

        return [
            'job_title'      => $jobTitle,
            'equipment_type' => $equipmentType,
            'company'        => $companyResource,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties'           => [
                'job_title'      => ['type' => ['string']],
                'equipment_type' => ['type' => ['string', 'null']],
                'company'        => CompanyResource::jsonSchema(),
            ],
            'required'             => [
                'job_title',
                'equipment_type',
                'company',
            ],
            'additionalProperties' => false,
        ];
    }
}
