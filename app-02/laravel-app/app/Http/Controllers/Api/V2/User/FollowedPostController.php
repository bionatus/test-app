<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\User\FollowedPost\IndexRequest;
use App\Http\Resources\Api\V2\Post\BaseResource;
use App\Models\Post;
use App\Models\Post\Scopes\ByAnyTaggable;
use App\Models\Post\Scopes\BySearchString;
use App\Models\Scopes\ByCreatedBefore;
use App\Models\Scopes\Latest;
use App\Models\User;
use App\Models\UserTaggable;
use App\Types\TaggablesCollection;
use Auth;
use Illuminate\Support\Carbon;

class FollowedPostController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $userFollowedTaggables = $user->followedTags->map(function(UserTaggable $userTaggable) {
            return $userTaggable->taggable;
        })->filter();

        $pageQuery = Post::withCount('comments');
        $pageQuery->scoped(new ByAnyTaggable(new TaggablesCollection($userFollowedTaggables)))
            ->scoped(new Latest())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)));

        if ($request->get(RequestKeys::CREATED_BEFORE)) {
            $pageQuery->scoped(new ByCreatedBefore(new Carbon($request->get(RequestKeys::CREATED_BEFORE))));
        }

        $page = $pageQuery->paginate();

        $page->appends($request->validated());

        return BaseResource::collection($page);
    }
}
