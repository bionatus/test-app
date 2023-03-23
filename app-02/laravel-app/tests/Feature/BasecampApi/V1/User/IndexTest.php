<?php

namespace Tests\Feature\BasecampApi\V1\User;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\BasecampApi\V1\UserController;
use App\Http\Requests\BasecampApi\V1\User\IndexRequest;
use App\Http\Resources\BasecampApi\V1\User\BriefResource;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see UserController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::BASECAMP_API_V1_USER_INDEX;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_returns_the_list_of_users_searching_by_id()
    {
        $users   = User::factory()->count(10)->create();
        $userIds = [];
        $users->take(5)->each(function(User $user) use (&$userIds) {
            $userIds[] = $user->getKey();
        });

        $route = URL::route($this->routeName, [RequestKeys::USERS => implode(',', $userIds)]);
        Config::set('basecamp.token.key', $key = 'test_key');
        $token = Hash::make($key);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BriefResource::jsonSchema()), $response);
        $data = $response->json('data');
        $this->assertCount(count($userIds), $data);

        $users = Collection::make($data);
        $users->each(function(array $user) use ($userIds) {
            $this->assertTrue(in_array($user['id'], $userIds));
        });
    }

    /** @test */
    public function it_returns_the_list_of_users_searching_by_search_string()
    {
        $userOne = User::factory()->create(['first_name' => 'Name']);
        $userTwo = User::factory()->create(['last_name' => 'Lastname']);
        User::factory()->create(['name' => 'Other']);

        $expectedUserKeys = [
            $userOne->getKey(),
            $userTwo->getKey(),
        ];

        $route = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'ame']);
        Config::set('basecamp.token.key', $key = 'test_key');
        $token = Hash::make($key);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BriefResource::jsonSchema()), $response);
        $data  = $response->json('data');
        $users = Collection::make($data);

        $this->assertCount(2, $data);
        $users->each(function(array $user) use ($expectedUserKeys) {
            $this->assertTrue(in_array($user['id'], $expectedUserKeys));
        });
    }

    /** @test */
    public function it_returns_the_list_of_users_ignoring_non_existing_users()
    {
        $users   = User::factory()->count(5)->create();
        $userIds = $users->pluck('id')->add(100)->add(101)->toArray();

        $route = URL::route($this->routeName, [RequestKeys::USERS => implode(',', $userIds)]);
        Config::set('basecamp.token.key', $key = 'test_key');
        $token = Hash::make($key);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');

        $this->assertCount(5, $data);
    }
}
