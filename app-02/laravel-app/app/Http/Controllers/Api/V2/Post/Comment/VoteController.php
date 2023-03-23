<?php

namespace App\Http\Controllers\Api\V2\Post\Comment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Scopes\ByUser;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VoteController extends Controller
{
    /** @noinspection PhpUnusedParameterInspection */
    public function store(Post $post, Comment $comment)
    {
        $comment->votes()->firstOrCreate(['user_id' => Auth::id()]);

        return (new BaseResource($comment))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function delete(Post $post, Comment $comment)
    {
        $user = Auth::user();

        $comment->votes()->scoped(new ByUser($user))->delete();

        return Response::noContent();
    }
}
