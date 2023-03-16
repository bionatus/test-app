<?php

namespace Tests\Feature\LiveApi\V1\Setting;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\SettingController;
use App\Http\Resources\LiveApi\V1\Setting\BaseResource;
use App\Models\Setting;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SettingController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_SETTING_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_supplier_settings()
    {
        $staff            = Staff::factory()->createQuietly();
        $expectedSettings = Setting::factory()->applicableToSupplier()->count(3)->create();
        Setting::factory()->groupAgent()->count(2)->create();
        Setting::factory()->groupNotification()->applicableToUser()->count(2)->create();

        $route = URL::route($this->routeName);
        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $data->each(function(array $rawSetting, int $index) use ($expectedSettings) {
            $setting = $expectedSettings->get($index);
            $this->assertSame($setting->getRouteKey(), $rawSetting['id']);
        });
    }

    /** @test */
    public function it_displays_a_list_of_supplier_notification_settings_filtered_by_group()
    {
        $staff            = Staff::factory()->createQuietly();
        $expectedSettings = Setting::factory()->groupNotification()->applicableToSupplier()->count(3)->create();
        Setting::factory()->groupValidation()->applicableToSupplier()->create();
        Setting::factory()->groupAgent()->count(2)->create();
        Setting::factory()->groupNotification()->applicableToUser()->count(2)->create();

        $route = URL::route($this->routeName, ['group' => 'notification']);
        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $data->each(function(array $rawSetting, int $index) use ($expectedSettings) {
            $setting = $expectedSettings->get($index);
            $this->assertSame($setting->getRouteKey(), $rawSetting['id']);
        });
    }
}
