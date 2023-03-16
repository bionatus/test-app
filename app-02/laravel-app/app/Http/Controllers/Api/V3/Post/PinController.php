<?php

namespace App\Http\Controllers\Api\V3\Post;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Post;
use App\Models\Post\Scopes\ByPinned;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PinController extends Controller
{
    public function store(Post $post)
    {
        Post::scoped(new ByPinned(true))->update(['pinned' => false]);

        $post->pinned = true;
        $post->save();

        return (new BaseResource($post))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Post $post)
    {
        $post->pinned = false;
        $post->save();

        return Response::noContent();
    }
}
