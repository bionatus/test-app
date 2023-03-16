<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Setting\IndexRequest;
use App\Http\Resources\LiveApi\V1\Setting\BaseResource;
use App\Models\Scopes\BySupplier;
use App\Models\Setting;
use App\Models\Setting\Scopes\ByApplicableTo;
use App\Models\Setting\Scopes\ByGroup;
use App\Models\Supplier;
use Auth;

class SettingController extends Controller
{
    public function index(IndexRequest $request)
    {
        $group    = $request->get(RequestKeys::GROUP);
        $supplier = Auth::user()->supplier;

        $settingQuery = Setting::scoped(new ByApplicableTo(Supplier::MORPH_ALIAS))->with([
            'settingSuppliers' => function($query) use ($supplier) {
                $query->scoped(new BySupplier($supplier));
            },
        ]);

        if ($group) {
            $settingQuery->scoped(new ByGroup($group));
        }

        $settings = $settingQuery->get();

        return BaseResource::collection($settings);
    }

    public function show(Setting $setting)
    {
        return new BaseResource($setting);
    }
}
