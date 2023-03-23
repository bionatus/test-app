<?php

namespace Tests\Feature\Api\V3\Account;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\Account\DetailedResource;
use App\Models\AppVersion;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AccountController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_the_user_account_information_without_client_version()
    {
        $user = User::factory()->create();
        AppVersion::factory()->create();
        SettingUser::factory()->usingUser($user)->count(2)->create();
        SettingUser::factory()->count(3)->create();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
        $this->assertCount($user->allSettingUsers()->count(), $data['settings']['data']);
    }

    /** @test */
    public function it_displays_the_user_account_information_with_client_version()
    {
        $user = User::factory()->create();
        AppVersion::factory()->create();
        SettingUser::factory()->usingUser($user)->count(2)->create();
        SettingUser::factory()->count(3)->create();

        $this->login($user);
        $response = $this->getWithParameters(URL::route($this->routeName), [RequestKeys::VERSION => '0.0.0']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
        $this->assertCount($user->allSettingUsers()->count(), $data['settings']['data']);
    }
}
