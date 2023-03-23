<?php

namespace Tests\Feature\Api\V4\Account\Profile;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Account\ProfileController;
use App\Http\Resources\Api\V4\Account\Profile\BaseResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ProfileController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V4_ACCOUNT_PROFILE_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_the_user_profile_information()
    {
        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
    }
}
