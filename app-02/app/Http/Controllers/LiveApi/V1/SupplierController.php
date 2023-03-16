<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Supplier\UpdateRequest;
use App\Http\Resources\LiveApi\V1\Supplier\BaseResource;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByType;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SupplierController extends Controller
{
    public function show(): JsonResponse
    {
        $supplier = Auth::user()->supplier;

        $supplier->load([
            'counters.settingStaffs',
        ]);

        $response = (new BaseResource($supplier))->response();

        if (!$supplier->welcome_displayed_at) {
            $supplier->welcome_displayed_at = Carbon::now();
            $supplier->save();
        }

        return $response;
    }

    public function update(UpdateRequest $request): BaseResource
    {
        $supplier = Auth::user()->supplier;
        /** @var Setting $smsNotificationSetting */
        $smsNotificationSetting = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first();
        /** @var Setting $emailNotificationSetting */
        $emailNotificationSetting = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first();
        $supplierFields           = [
            RequestKeys::NAME,
            RequestKeys::EMAIL,
            RequestKeys::BRANCH,
            RequestKeys::PHONE,
            RequestKeys::PROKEEP_PHONE,
            RequestKeys::ADDRESS,
            RequestKeys::ADDRESS_2,
            RequestKeys::ZIP_CODE,
            RequestKeys::CITY,
            RequestKeys::STATE,
            RequestKeys::COUNTRY,
            RequestKeys::TIMEZONE,
            RequestKeys::ABOUT,
            RequestKeys::CONTACT_EMAIL,
            RequestKeys::CONTACT_SECONDARY_EMAIL,
            RequestKeys::CONTACT_PHONE,
            RequestKeys::OFFERS_DELIVERY,
        ];

        $this->storeImageFromRequest($request, $supplier, RequestKeys::IMAGE, MediaCollectionNames::IMAGES);
        $this->storeImageFromRequest($request, $supplier, RequestKeys::LOGO, MediaCollectionNames::LOGO);
        $supplier->fill($request->only($supplierFields));
        $supplier->verify();
        $supplier->save();

        $supplier->staff()->scoped(new ByType(Staff::TYPE_OWNER))->update([
            'email' => $request->get(RequestKeys::EMAIL),
        ]);

        $supplier->staff()->updateOrCreate(['type' => Staff::TYPE_ACCOUNTANT], [
            'name'     => $request->get(RequestKeys::ACCOUNTANT_NAME),
            'email'    => $request->get(RequestKeys::ACCOUNTANT_EMAIL),
            'phone'    => $request->get(RequestKeys::ACCOUNTANT_PHONE),
            'password' => '',
        ]);

        $supplier->staff()->updateOrCreate(['type' => Staff::TYPE_MANAGER], [
            'name'     => $request->get(RequestKeys::MANAGER_NAME),
            'email'    => $request->get(RequestKeys::MANAGER_EMAIL),
            'phone'    => $request->get(RequestKeys::MANAGER_PHONE),
            'password' => '',
        ]);

        $counterStaffs         = Collection::make($request->get(RequestKeys::COUNTER_STAFF));
        $supplierCounterStaffs = $supplier->staff()->scoped(new ByType(Staff::TYPE_COUNTER))->get();

        $newStaffs    = $counterStaffs->whereNotIn('email', $supplierCounterStaffs->pluck('email'));
        $oldStaffs    = $supplierCounterStaffs->whereIn('email', $counterStaffs->pluck('email'));
        $deleteStaffs = $supplierCounterStaffs->whereNotIn('email', $counterStaffs->pluck('email'));

        $newStaffs->each(function($dataStaff) use ($smsNotificationSetting, $emailNotificationSetting, $supplier) {
            $this->createStaff($supplier, $dataStaff, $emailNotificationSetting, $smsNotificationSetting);
        });

        $oldStaffs->each(function(Staff $staff) use (
            $smsNotificationSetting,
            $emailNotificationSetting,
            $counterStaffs
        ) {
            $dataStaff = $counterStaffs->firstWhere('email', $staff->email);
            $this->updateStaff($staff, $dataStaff, $emailNotificationSetting, $smsNotificationSetting);
        });

        $deleteStaffs->each(function(Staff $staff) {
            $staff->delete();
        });

        $supplier->refresh();
        $supplier->load([
            'counters.settingStaffs',
        ]);

        return new BaseResource($supplier);
    }

    private function createStaff(
        Supplier $supplier,
        array $dataStaff,
        Setting $emailSetting,
        Setting $smsSetting
    ) {
        /** @var Staff $createdStaff */
        $createdStaff = $supplier->staff()->create([
            'type'     => Staff::TYPE_COUNTER,
            'name'     => $dataStaff[RequestKeys::NAME] ?? null,
            'email'    => $dataStaff[RequestKeys::EMAIL] ?? null,
            'password' => '',
            'phone'    => $dataStaff[RequestKeys::PHONE] ?? null,
        ]);

        $this->createOrUpdateSettings($createdStaff, $dataStaff, $emailSetting, $smsSetting);
    }

    private function updateStaff(
        Staff $staff,
        array $dataStaff,
        Setting $emailSetting,
        Setting $smsSetting
    ) {
        $staff->update([
            'name'  => $dataStaff[RequestKeys::NAME] ?? null,
            'email' => $dataStaff[RequestKeys::EMAIL] ?? null,
            'phone' => $dataStaff[RequestKeys::PHONE] ?? null,
        ]);

        $this->createOrUpdateSettings($staff, $dataStaff, $emailSetting, $smsSetting);
    }

    private function createOrUpdateSettings(
        Staff $staff,
        array $dataStaff,
        Setting $emailSetting,
        Setting $smsSetting
    ) {
        $staff->settingStaffs()
            ->updateOrCreate(['setting_id' => $emailSetting->getKey()],
                ['value' => $dataStaff[RequestKeys::STAFF_EMAIL_NOTIFICATION] ?? $emailSetting->value]);
        $staff->settingStaffs()
            ->updateOrCreate(['setting_id' => $smsSetting->getKey()],
                ['value' => $dataStaff[RequestKeys::STAFF_SMS_NOTIFICATION] ?? $smsSetting->value]);
    }

    function storeImageFromRequest(UpdateRequest $request, Supplier $supplier, string $file, string $mediaCollection)
    {
        if ($request->hasFile($file)) {
            try {
                $supplier->clearMediaCollection($mediaCollection);
                $supplier->addMediaFromRequest($file)->toMediaCollection($mediaCollection);
            } catch (Exception $exception) {
                // Silently ignored
            }
        }
    }
}
