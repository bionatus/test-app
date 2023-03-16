<?php

namespace Tests\Feature\Api\V3\AppSetting;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\AppSettingController;
use App\Http\Resources\Api\V3\AppSetting\BaseResource;
use App\Models\AppSetting;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AppSettingController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_APP_SETTING_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, AppSetting::factory()->create()));
    }

    /** @test */
    public function it_displays_an_app_setting()
    {
        $appSetting = AppSetting::factory()->create();
        $route      = URL::route($this->routeName, $appSetting);
        $supplier   = Supplier::factory()->createQuietly();

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $appSetting->getRouteKey());
    }
}
