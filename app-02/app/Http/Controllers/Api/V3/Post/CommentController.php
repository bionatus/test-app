<?php

namespace App\Http\Controllers\Api\V3\Post;

use App\Actions\Models\Activity\BuildResource;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Events\Post\Comment\Created;
use App\Events\Post\Comment\UserTagged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Post\Comment\StoreRequest;
use App\Http\Requests\Api\V3\Post\Comment\UpdateRequest;
use App\Http\Resources\Api\V2\Activity\CommentResource;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Scopes\ByKeys;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Support\Carbon;

class CommentController extends Controller
{
    public function store(StoreRequest $request, Post $post): BaseResource
    {
        /** @var Comment $comment */
        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'message' => $request->get(RequestKeys::MESSAGE),
        ]);

        $users = $request->get(RequestKeys::USERS);
        if ($users) {
            $comment->taggedUsers()->attach($users);
            $comment->taggedUsers()->each(function(User $taggedUser) use ($comment) {
                UserTagged::dispatch($comment, $taggedUser);
            });
        }
        $comment->loadMissing(['taggedUsers']);

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }
        }
        $comment->load('media');

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
        $syncInfo         = $comment->taggedUsers()->sync($request->get(RequestKeys::USERS));

        if ($request->has(RequestKeys::CURRENT_IMAGES)) {
            $mediaCollection = $comment->getMedia(MediaCollectionNames::IMAGES)
                ->whereIn('uuid', $request->get(RequestKeys::CURRENT_IMAGES));
            $comment->clearMediaCollectionExcept(MediaCollectionNames::IMAGES, $mediaCollection);
        }

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $comment->content_updated_at = Carbon::now();
                    $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }
        }

        $comment->save();
        $comment->load(['media', 'taggedUsers']);

        if ($syncInfo['attached']) {
            $newTaggedUsers = User::query()->scoped(new ByKeys($syncInfo['attached']))->get();
            $newTaggedUsers->each(function(User $newTaggedUser) use ($comment) {
                UserTagged::dispatch($comment, $newTaggedUser);
            });
        }

        return new BaseResource($comment);
    }
}
