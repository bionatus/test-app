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
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_SETTING_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, Setting::factory()->applicableToSupplier()->create()));
    }

    /** @test */
    public function it_displays_a_setting()
    {
        $setting = Setting::factory()->applicableToSupplier()->create();
        $route   = URL::route($this->routeName, $setting);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $setting->getRouteKey());
    }
}
