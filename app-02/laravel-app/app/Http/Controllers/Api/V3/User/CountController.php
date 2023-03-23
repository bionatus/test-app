<?php

namespace App\Http\Controllers\Api\V3\User;

use App\Constants\CacheKeys;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\User\Count\BaseResource;
use App\Models\User;
use App\Models\User\Scopes\RegistrationCompleted;
use Cache;

class CountController extends Controller
{
    public function __invoke()
    {
        $usersCount = Cache::remember(CacheKeys::USERS_COUNT, 60 * 60 * 24, function () {
            return User::scoped(new RegistrationCompleted())->count();
        });

        return new BaseResource($usersCount);
    }
}
