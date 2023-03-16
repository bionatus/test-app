<?php

namespace App\Http\Controllers\LiveApi\V1\Setting;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Setting\BulkNotification\StoreRequest;
use App\Http\Resources\LiveApi\V1\Setting\BaseResource;
use App\Models\Scopes\ByRouteKeys;
use App\Models\Scopes\BySupplier;
use App\Models\Setting;
use Auth;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseHttp;

class BulkNotificationController extends Controller
{
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
