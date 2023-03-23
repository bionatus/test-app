<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\SupportCall\StoreRequest;
use App\Http\Resources\Api\V3\SupportCall\BaseResource;
use App\Models\Oem;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByUuid;
use App\Models\SupportCall;
use App\Models\SupportCallCategory;
use Auth;

class SupportCallController extends Controller
{
    public function store(StoreRequest $request)
    {
        $slug = $request->get(RequestKeys::CATEGORY);

        if ($slug !== SupportCall::CATEGORY_OEM) {
            /** @var SupportCallCategory $supportCallCategory */
            $supportCallCategory = SupportCallCategory::with('parent')->scoped(new ByRouteKey($slug))->first();
            $parent              = $supportCallCategory->parent;

            $attributes = [
                'category'    => $parent ? $parent->getRouteKey() : $slug,
                'subcategory' => $parent ? $slug : null,
            ];
        } else {
            $oem        = Oem::scoped(new ByUuid($request->get(RequestKeys::OEM)))->first();
            $attributes = [
                'category' => $slug,
                'oem_id'   => $oem->getKey(),
            ];
        }

        $attributes['user_id'] = Auth::id();

        $supportCall = SupportCall::create($attributes);

        return new BaseResource($supportCall);
    }
}
