<?php

namespace Tests\Feature\Api\V2\User\Setting;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\User\SettingController;
use App\Http\Requests\Api\V2\User\Setting\UpdateRequest;
use App\Http\Resources\Api\V2\User\Setting\BaseResource;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SettingController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_USER_SETTING_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName, Setting::factory()->create()));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_update_a_user_setting()
    {
        $user    = User::factory()->create();
        $setting = Setting::factory()->boolean()->create();
        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $currentValue = 0]);
        $route = URL::route($this->routeName, $setting);

        $this->assertDatabaseHas(SettingUser::tableName(), [
            'setting_id' => $setting->getKey(),
            'user_id'    => $user->getKey(),
            'value'      => $currentValue,
        ]);

        $value = 1;

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::VALUE => $value,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $setting->getRouteKey());
        $this->assertEquals($data['value'], $value);

        $this->assertDatabaseHas(SettingUser::tableName(), [
            'setting_id' => $setting->getKey(),
            'user_id'    => $user->getKey(),
            'value'      => $value,
        ]);
    }

    /** @test */
    public function it_create_a_user_setting_if_was_not_previously_set()
    {
        $user    = User::factory()->create();
        $setting = Setting::factory()->boolean()->create();
        $route   = URL::route($this->routeName, $setting);

        $value = 1;

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::VALUE => $value,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $setting->getRouteKey());
        $this->assertEquals($data['value'], $value);

        $this->assertDatabaseHas(SettingUser::tableName(), [
            'setting_id' => $setting->getKey(),
            'user_id'    => $user->getKey(),
            'value'      => $value,
        ]);
    }

    /** @test */
    public function it_does_not_create_a_user_setting_if_is_a_supplier_setting()
    {
        $user        = User::factory()->create();
        $settingUser = Setting::factory()->applicableToSupplier()->boolean()->create();
        $route       = URL::route($this->routeName, $settingUser);

        $value = 1;

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::VALUE => $value,
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertDatabaseMissing(SettingUser::tableName(), [
            'setting_id' => $settingUser->getKey(),
            'user_id'    => $user->getKey(),
            'value'      => $value,
        ]);
    }
}
