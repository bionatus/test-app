<?php

namespace App\Http\Resources\Api\V2\Support\Ticket\AgentHistory;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\UserResource as BaseResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    private BaseResource $baseResource;

    public function __construct(User $resource)
    {
        $this->baseResource = new BaseResource($resource);

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseResource::jsonSchema();
    }
}
