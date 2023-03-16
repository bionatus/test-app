<?php

namespace App\Http\Controllers\Api\V3\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Post\Scopes\PinnedFirst;
use App\Models\Scopes\Latest;
use App\Models\User;
use Request;

class PostController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        $posts = $user->posts()
            ->withCount(['comments', 'votes'])
            ->with('solutionComment.user')
            ->scoped(new PinnedFirst())
            ->scoped(new Latest())
            ->paginate();

        return BaseResource::collection($posts);
    }
}
