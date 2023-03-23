<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\NotificationSetting\StoreRequest;
use App\Http\Resources\LiveApi\V1\NotificationSetting\BaseResource;
use App\Models\Scopes\ByRouteKeys;
use App\Models\Scopes\BySupplier;
use App\Models\Setting;
use App\Models\Setting\Scopes\ByApplicableTo;
use App\Models\Setting\Scopes\ByGroup;
use App\Models\Supplier;
use Auth;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseHttp;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $supplier = Auth::user()->supplier;

        $settings = Setting::scoped(new ByGroup(Setting::GROUP_NOTIFICATION))
            ->scoped(new ByApplicableTo(Supplier::MORPH_ALIAS))
            ->with([
                'settingSuppliers' => function($query) use ($supplier) {
                    $query->scoped(new BySupplier($supplier));
                },
            ])
            ->get();

        return BaseResource::collection($settings);
    }

    public function store(StoreRequest $request)
    {
        $supplier = Auth::user()->supplier;

        $requestedSettings = Collection::make($request->get(RequestKeys::SETTINGS));

        $settings = Setting::scoped(new ByRouteKeys($requestedSettings->keys()))->get();

        $requestedSettings->each(function(bool $requestedValue, string $requestedSlug) use (
            $supplier,
            $settings
        ) {
            $setting = $settings->first(function(Setting $setting) use ($requestedSlug) {
                return $setting->slug === $requestedSlug;
            });
            $setting->settingSuppliers()->updateOrCreate([
                'setting_id'  => $setting->getKey(),
                'supplier_id' => $supplier->getKey(),
            ], ['value' => $requestedValue]);
        });

        $settings->load([
            'settingSuppliers' => function($query) use ($supplier) {
                $query->scoped(new BySupplier($supplier));
            },
        ]);

        return BaseResource::collection($settings)->response()->setStatusCode(ResponseHttp::HTTP_CREATED);
    }
}
