<?php

namespace App\Http\Controllers\Api\V3\Post;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Post;
use App\Models\Scopes\ExceptKey;
use App\Models\User;
use App\Models\User\Scopes\Alphabetically;
use App\Models\User\Scopes\ByEnabled;
use App\Models\User\Scopes\ByPostOrComments;
use Auth;

class UserController extends Controller
{
    public function __invoke(Post $post)
    {
        $users = User::scoped(new ByPostOrComments($post))
            ->scoped(new ExceptKey(Auth::user()->getKey()))
            ->scoped(new ByEnabled())
            ->scoped(new Alphabetically())
            ->paginate();

        return UserResource::collection($users);
    }
}
