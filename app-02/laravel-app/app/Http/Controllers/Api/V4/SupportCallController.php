<?php

namespace App\Http\Controllers\Api\V4;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\SupportCall\StoreRequest;
use App\Http\Resources\Api\V4\SupportCall\BaseResource;
use App\Models\Brand;
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

        switch ($slug) {

            case SupportCall::CATEGORY_OEM:
                $oem        = Oem::scoped(new ByUuid($request->get(RequestKeys::OEM)))->first();
                $attributes = [
                    'category' => $slug,
                    'oem_id'   => $oem->getKey(),
                ];
                break;

            case SupportCall::CATEGORY_MISSING_OEM:
                $brand      = Brand::scoped(new ByRouteKey($request->get(RequestKeys::MISSING_OEM_BRAND)))->first();
                $attributes = [
                    'category'                 => $slug,
                    'missing_oem_brand_id'     => $brand->getKey(),
                    'missing_oem_model_number' => $request->get(RequestKeys::MISSING_OEM_MODEL_NUMBER),
                ];
                break;

            default:
                /** @var SupportCallCategory $supportCallCategory */

                $supportCallCategory = SupportCallCategory::with('parent')->scoped(new ByRouteKey($slug))->first();
                $parent              = $supportCallCategory->parent;
                $attributes          = [
                    'category'    => $parent ? $parent->getRouteKey() : $slug,
                    'subcategory' => $parent ? $slug : null,
                ];
                break;
        }

        $attributes['user_id'] = Auth::id();
        $supportCall           = SupportCall::create($attributes);

        return new BaseResource($supportCall);
    }
}
