<?php

namespace Tests\Feature\BasecampApi\V1\User;

use App\Constants\RouteNames;
use App\Http\Controllers\BasecampApi\V1\UserController;
use App\Http\Resources\BasecampApi\V1\User\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see UserController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::BASECAMP_API_V1_USER_SHOW;

    /** @test */
    public function it_returns_the_user_information()
    {

        $user    = User::factory()->create();
        $route   = URL::route($this->routeName, [$user]);
        $company = Company::factory()->create(['address' => 'address']);
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();

        Config::set('basecamp.token.key', $key = 'test_key');
        $token    = Hash::make($key);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));

        $this->assertSame($user->getKey(), $data['id']);
    }
}
