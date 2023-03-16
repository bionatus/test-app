<?php

namespace App\Http\Resources\AutomationApi\V1\Mobile\SignupProcess;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AuthenticationCodeResource;
use App\Models\AuthenticationCode;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AuthenticationCode $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private AuthenticationCodeResource $authenticationCodeResource;

    public function __construct(AuthenticationCode $resource)
    {
        parent::__construct($resource);

        $this->authenticationCodeResource = new AuthenticationCodeResource($resource);
    }

    public function toArray($request)
    {
        return $this->authenticationCodeResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return AuthenticationCodeResource::jsonSchema();
    }
}
