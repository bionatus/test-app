<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\User\IndexRequest;
use App\Http\Resources\Api\V3\User\BaseResource;
use App\Models\Scopes\ExceptKey;
use App\Models\User;
use App\Models\User\Scopes\Alphabetically;
use App\Models\User\Scopes\ByEnabled;
use App\Models\User\Scopes\ByFullOrPublicName;
use Auth;

class UserController extends Controller
{
    public function index(IndexRequest $request)
    {
        $searchString = $request->get(RequestKeys::SEARCH_STRING);

        $users = User::scoped(new ByFullOrPublicName($searchString))
            ->scoped(new ByEnabled())
            ->scoped(new ExceptKey(Auth::user()->getKey()))
            ->scoped(new Alphabetically())
            ->paginate();

        return BaseResource::collection($users);
    }

    public function show(User $user)
    {
        return new BaseResource($user);
    }
}
