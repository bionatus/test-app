<?php

namespace App\Http\Resources\Api\V3\Auth\Refresh;

use App\Http\Resources\Api\V3\Auth\UserResource;
use App\Models\User;

class BaseResource extends UserResource
{
    public function __construct(User $resource, string $token)
    {
        parent::__construct($resource, $token);
    }
}
