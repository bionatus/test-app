<?php

namespace Tests\Feature\LiveApi\V1\Setting\BulkNotification;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Setting\BulkNotificationController;
use App\Http\Requests\LiveApi\V1\Setting\BulkNotification\StoreRequest;
use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see BulkNotificationController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_SETTING_BULK_NOTIFICATION_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_creates_supplier_notification_settings()
    {
        $setting        = Setting::factory()->groupNotification()->applicableToSupplier()->create();
        $anotherSetting = Setting::factory()->groupNotification()->applicableToSupplier()->create();

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::SETTINGS => [
                $setting->getRouteKey()        => true,
                $anotherSetting->getRouteKey() => false,
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SettingSupplier::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'setting_id'  => $setting->getKey(),
            'value'       => true,
        ]);
        $this->assertDatabaseHas(SettingSupplier::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'setting_id'  => $anotherSetting->getKey(),
            'value'       => false,
        ]);
    }

    /** @test */
    public function it_updates_supplier_notification_settings()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;

        $setting        = Setting::factory()->groupNotification()->applicableToSupplier()->create();
        $anotherSetting = Setting::factory()->groupNotification()->applicableToSupplier()->create();

        SettingSupplier::factory()->usingSetting($setting)->createQuietly(['value' => true]);
        SettingSupplier::factory()->usingSetting($anotherSetting)->createQuietly(['value' => false]);

        SettingSupplier::factory()->usingSupplier($supplier)->usingSetting($setting)->createQuietly(['value' => false]);
        SettingSupplier::factory()
            ->usingSupplier($supplier)
            ->usingSetting($anotherSetting)
            ->createQuietly(['value' => true]);

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::SETTINGS => [
                $setting->getRouteKey()        => true,
                $anotherSetting->getRouteKey() => false,
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SettingSupplier::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'setting_id'  => $setting->getKey(),
            'value'       => true,
        ]);
        $this->assertDatabaseHas(SettingSupplier::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'setting_id'  => $anotherSetting->getKey(),
            'value'       => false,
        ]);
    }
}
