<?php

namespace Tests\Feature\Api\V3\User;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\UserController;
use App\Http\Resources\Api\V3\User\BaseResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see UserController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_USER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $user = User::factory()->create();
        $this->get(URL::route($this->routeName, [$user]));
    }

    /** @test */
    public function it_returns_the_user_information()
    {
        $user  = User::factory()->create();

        $this->login(User::factory()->create());
        $response = $this->get(URL::route($this->routeName, [$user]));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
    }
}
