<?php

namespace Tests\Feature\Api\V3\User\Count;

use App\Constants\CacheKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\User\CountController;
use App\Http\Resources\Api\V3\User\Count\BaseResource;
use App\Models\User;
use Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see CountController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_USER_COUNT;

    /** @test */
    public function it_returns_the_count_of_users_with_registration_completed()
    {
        User::factory()->count(5000)->create(['registration_completed' => true]);
        User::factory()->count(2000)->create();

        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals('5k', $data->get('users_count'));
    }

    /** @test */
    public function it_saves_in_cache_the_users_count()
    {
        User::factory()->count(5000)->create(['registration_completed' => true]);
        $this->assertFalse(Cache::has(CacheKeys::USERS_COUNT));
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue(Cache::has(CacheKeys::USERS_COUNT));
    }
}
