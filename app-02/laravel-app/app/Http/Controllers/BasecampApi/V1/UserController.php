<?php

namespace App\Http\Controllers\BasecampApi\V1;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\BasecampApi\V1\User\IndexRequest;
use App\Http\Resources\BasecampApi\V1\User\BaseResource;
use App\Http\Resources\BasecampApi\V1\User\BriefResource;
use App\Models\Scopes\ByRouteKeys;
use App\Models\User;
use App\Models\User\Scopes\ByFullName;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    public function index(IndexRequest $request)
    {
        $users = Collection::make();

        if ($request->has(RequestKeys::USERS)) {
            $keys  = explode(',', $request->get(RequestKeys::USERS));
            $users = User::scoped(new ByRouteKeys($keys))->get();
        }

        if ($request->has(RequestKeys::SEARCH_STRING)) {
            $users = User::scoped(new ByFullName($request->get(RequestKeys::SEARCH_STRING)))->get();
        }

        return BriefResource::collection($users);
    }

    public function show(User $user)
    {
        return new BaseResource($user);
    }
}
