<?php

namespace App\Http\Controllers\Api\V2\Post;

use App\Actions\Models\Activity\BuildResource;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Events\Post\Comment\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Post\Comment\IndexRequest;
use App\Http\Requests\Api\V2\Post\Comment\StoreRequest;
use App\Http\Requests\Api\V2\Post\Comment\UpdateRequest;
use App\Http\Resources\Api\V2\Activity\CommentResource;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Comment\Scopes\Oldest;
use App\Models\Comment\Scopes\SolutionsFirst;
use App\Models\Post;
use App\Models\Scopes\ByCreatedBefore;
use Auth;
use Exception;
use Illuminate\Support\Carbon;
use Response;

class CommentController extends Controller
{
    public function index(IndexRequest $request, Post $post)
    {
        $post->load(['comments.authUserVote', 'comments.latestFiveVotes']);

        $pageQuery = $post->comments()->scoped(new SolutionsFirst())->scoped(new Oldest());

        if ($request->get(RequestKeys::CREATED_BEFORE)) {
            $pageQuery->scoped(new ByCreatedBefore(new Carbon($request->get(RequestKeys::CREATED_BEFORE))));
        }

        return BaseResource::collection($pageQuery->paginate());
    }

    public function store(StoreRequest $request, Post $post): BaseResource
    {
        /** @var Comment $comment */
        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'message' => $request->get(RequestKeys::MESSAGE),
        ]);

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }
        }
        Created::dispatch($comment);

        $property = (new BuildResource($comment, CommentResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_CREATED, Activity::RESOURCE_COMMENT, $comment, Auth::getUser(),
            $property);

        return new BaseResource($comment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function update(UpdateRequest $request, Post $post, Comment $comment): BaseResource
    {
        $comment->message = $request->get(RequestKeys::MESSAGE);

        if ($request->has(RequestKeys::CURRENT_IMAGES)) {
            $mediaCollection = $comment->getMedia(MediaCollectionNames::IMAGES)
                ->whereIn('uuid', $request->get(RequestKeys::CURRENT_IMAGES));
            $comment->clearMediaCollectionExcept(MediaCollectionNames::IMAGES, $mediaCollection);
        }

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }
        }

        $comment->save();
        $comment->load('media');

        return new BaseResource($comment);
    }

    /**
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function delete(Post $post, Comment $comment)
    {
        $property = (new BuildResource($comment, CommentResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_DELETED, Activity::RESOURCE_COMMENT, $comment, Auth::getUser(),
            $property);

        $comment->delete();

        return Response::noContent();
    }
}
