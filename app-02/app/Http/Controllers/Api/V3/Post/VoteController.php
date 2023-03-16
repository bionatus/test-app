<?php

namespace App\Http\Controllers\Api\V3\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Post;
use App\Models\Scopes\ByUser;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VoteController extends Controller
{
    public function store(Post $post)
    {
        $post->votes()->firstOrCreate(['user_id' => Auth::id()]);

        return (new BaseResource($post))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Post $post)
    {
        $user = Auth::user();

        $post->votes()->scoped(new ByUser($user))->delete();

        return Response::noContent();
    }
}
