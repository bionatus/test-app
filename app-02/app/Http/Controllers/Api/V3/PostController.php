<?php

namespace App\Http\Controllers\Api\V3;

use App\Actions\Models\Activity\BuildResource;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Events\Post\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Post\IndexRequest;
use App\Http\Requests\Api\V3\Post\StoreRequest;
use App\Http\Requests\Api\V3\Post\UpdateRequest;
use App\Http\Resources\Api\V2\Activity\PostResource;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\IsTaggable;
use App\Models\Model;
use App\Models\Post;
use App\Models\Post\Scopes\BySearchString;
use App\Models\Post\Scopes\ByTaggableTypes;
use App\Models\Post\Scopes\PinnedFirst;
use App\Models\Post\Scopes\TaggableTypesQuantity;
use App\Models\Scopes\ByCreatedBefore;
use App\Models\Scopes\ByType;
use App\Models\Scopes\Latest;
use App\Models\Tag;
use App\Types\TaggablesCollection;
use Auth;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PostController extends Controller
{
    public function index(IndexRequest $request)
    {
        $validated = Collection::make($request->validated());

        $pageQuery = Post::withCount(['comments', 'solutionComment', 'votes'])->with('solutionComment.user');

        if ($request->taggableTypes()->isNotEmpty()) {
            $pageQuery->scoped(new TaggableTypesQuantity($request->taggableTypes()));
        }

        if ($request->get(RequestKeys::CREATED_BEFORE)) {
            $pageQuery->scoped(new ByCreatedBefore(new Carbon($request->get(RequestKeys::CREATED_BEFORE))));
        }

        if ($request->get(RequestKeys::TYPE)) {
            $pageQuery->scoped(new ByType($request->get(RequestKeys::TYPE)));
        }

        $pageQuery->scoped(new PinnedFirst())
            ->scoped(new Latest())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new ByTaggableTypes($request->taggableTypes()));

        $page = $pageQuery->paginate();
        $page->appends($validated->toArray());

        return BaseResource::collection($page);
    }

    public function store(StoreRequest $request): BaseResource
    {
        $post = Post::create([
            'user_id'   => Auth::id(),
            'message'   => $request->get(RequestKeys::MESSAGE),
            'type'      => $request->get(RequestKeys::TYPE, Post::TYPE_OTHER),
            'video_url' => $request->get(RequestKeys::VIDEO_URL),
        ]);

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $post->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }
        }

        if ($request->taggables()->isNotEmpty()) {
            $tags = $request->taggables()->map(function(IsTaggable $taggable) {
                /** @var Model $taggable */
                $tag = new Tag();
                $tag->taggable()->associate($taggable);

                return $tag;
            });
            $post->tags()->saveMany($tags);
        }

        $post->loadCount('comments');

        Created::dispatch($post);

        $property = (new BuildResource($post, PostResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_CREATED, Activity::RESOURCE_POST, $post, Auth::getUser(), $property);

        return new BaseResource($post);
    }

    public function update(UpdateRequest $request, Post $post): BaseResource
    {
        $post->loadcount('comments');
        $post->message   = $request->get(RequestKeys::MESSAGE);
        $post->video_url = $request->get(RequestKeys::VIDEO_URL);

        $this->syncTags($post, $request->taggables());

        if ($request->has(RequestKeys::CURRENT_IMAGES)) {
            $mediaCollection = $post->getMedia(MediaCollectionNames::IMAGES)
                ->whereIn('uuid', $request->get(RequestKeys::CURRENT_IMAGES));
            $post->clearMediaCollectionExcept(MediaCollectionNames::IMAGES, $mediaCollection);
        }

        if ($request->hasFile(RequestKeys::IMAGES)) {
            foreach ($request->file(RequestKeys::IMAGES) as $file) {
                try {
                    $post->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $exception) {
                    // Silently ignored
                }
            }
        }

        $post->save();
        $post->load('media');

        return new BaseResource($post);
    }

    private function syncTags(Post $post, TaggablesCollection $taggables): void
    {
        $newTags = $taggables->map(function(IsTaggable $taggable) {
            /** @var Model $taggable */
            $tag = new Tag();
            $tag->taggable()->associate($taggable);

            return $tag;
        });

        $currentTags = $post->tags;

        $toDelete = $currentTags->filter(function(Tag $currentTag) use ($newTags) {
            return !$newTags->contains(function(Tag $newTag) use ($currentTag) {
                return $currentTag->taggable_id === $newTag->taggable_id && $currentTag->taggable_type === $newTag->taggable_type;
            });
        })->pluck('id');

        if ($toDelete->count()) {
            $post->tags()->whereIn('id', $toDelete)->delete();
        }

        $toAdd = $newTags->filter(function(Tag $newTag) use ($currentTags) {
            return !$currentTags->contains(function(Tag $existingTag) use ($newTag) {
                return $existingTag->taggable_id === $newTag->taggable_id && $existingTag->taggable_type === $newTag->taggable_type;
            });
        });

        if ($toAdd->count()) {
            $post->tags()->saveMany($toAdd);
        }
        $post->load('tags');
    }
}
