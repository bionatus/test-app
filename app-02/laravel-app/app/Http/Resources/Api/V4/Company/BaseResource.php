<?php

namespace App\Http\Resources\Api\V4\Company;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Company $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private CompanyResource $companyResource;

    public function __construct(Company $resource)
    {
        parent::__construct($resource);

        $this->companyResource = new CompanyResource($resource);
    }

    public function toArray($request)
    {
        return $this->companyResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CompanyResource::jsonSchema();
    }
}
