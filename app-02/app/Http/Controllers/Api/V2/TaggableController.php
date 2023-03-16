<?php

namespace App\Http\Controllers\Api\V2;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Tag\IndexRequest;
use App\Http\Resources\Api\V2\Tag\DetailedResource;
use App\Http\Resources\Api\V2\Tag\ImagedResource;
use App\Models\IsTaggable;
use App\Models\User;
use App\Types\TaggableType;
use Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class TaggableController extends Controller
{
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $validated      = Collection::make($request->validated());
        $parentModelKey = null;
        if ($validated->has(RequestKeys::BRAND)) {
            $parentModelKey = $validated->get(RequestKeys::BRAND);
        } elseif ($validated->has(RequestKeys::SERIES)) {
            $parentModelKey = $validated->get(RequestKeys::SERIES);
        }

        $page = TaggableType::query($validated->get(RequestKeys::TYPE), $parentModelKey)
            ->paginate($validated->get(RequestKeys::PER_PAGE));
        $page->appends($validated->toArray());

        return ImagedResource::collection($page);
    }

    public function show(IsTaggable $taggable)
    {
        /** @var User $user */
        $user = Auth::user();

        return new DetailedResource($taggable->toTagType(true), $user);
    }
}
