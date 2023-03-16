<?php

namespace App\Http\Controllers\Api\V2\Taggable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Tag\DetailedResource;
use App\Models\IsTaggable;
use App\Models\Model;
use App\Models\User;
use App\Models\UserTaggable;
use App\Models\UserTaggable\Scopes\ByTaggable;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FollowController extends Controller
{
    public function store(IsTaggable $taggable)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isFollowing($taggable)) {
            /** @var Model $taggable */
            $userTaggable = new UserTaggable();
            $userTaggable->taggable()->associate($taggable);

            $user->followedTags()->save($userTaggable);
        }

        return (new DetailedResource($taggable->toTagType(true), $user))->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    public function delete(IsTaggable $taggable)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->followedTags()->scoped(new ByTaggable($taggable))->delete();

        return Response::noContent();
    }
}
