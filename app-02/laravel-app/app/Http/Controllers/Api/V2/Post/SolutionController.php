<?php

namespace App\Http\Controllers\Api\V2\Post;

use App\Actions\Models\Activity\BuildResource;
use App\Constants\RequestKeys;
use App\Events\Post\Solution\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Post\Solution\StoreRequest;
use App\Http\Resources\Api\V2\Activity\CommentResource;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Scopes\ByRouteKey;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SolutionController extends Controller
{
    public function store(StoreRequest $request, Post $post)
    {
        $post->comments()->update(['solution' => null]);

        /** @var Comment $solution */
        $solution = $post->comments()->scoped(new ByRouteKey($request->get(RequestKeys::SOLUTION)))->first();

        $solution->solution = true;
        $solution->save();

        Created::dispatch($solution);

        $property = (new BuildResource($solution, CommentResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_CREATED, Activity::RESOURCE_SOLUTION, $solution, Auth::getUser(),
            $property);

        return (new BaseResource($solution))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function delete(Post $post, Comment $comment)
    {
        $comment->solution = null;
        $comment->save();

        return Response::noContent();
    }
}
