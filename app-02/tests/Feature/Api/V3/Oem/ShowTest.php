<?php

namespace Tests\Feature\Api\V3\Oem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\OemController;
use App\Http\Requests\Api\V3\Oem\ShowRequest;
use App\Http\Resources\Api\V3\Oem\DetailedResource;
use App\Models\Layout;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OemController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_OEM_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, Oem::factory()->create()));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, ShowRequest::class);
    }

    /** @test */
    public function it_displays_an_oem()
    {
        $layout = Layout::factory()->create();
        $oem    = Oem::factory()->create();
        $route  = URL::route($this->routeName, $oem);
        $user   = User::factory()->create();

        $this->login($user);
        $response = $this->getWithParameters($route, [RequestKeys::VERSION => $layout->version]);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $oem->getRouteKey());
        $this->assertEquals($data['layout']['id'], $layout->getRouteKey());
    }

    /** @test */
    public function it_counts_the_number_of_times_a_user_accesses_to_an_oem()
    {
        $layout = Layout::factory()->create();
        $oem    = Oem::factory()->create();
        $route  = URL::route($this->routeName, $oem);
        $user   = User::factory()->create();

        $this->login($user);
        $response = $this->getWithParameters($route, [RequestKeys::VERSION => $layout->version]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $oem->getKey(),
            'user_id'               => $user->getKey(),
            'oem_search_counter_id' => null,
        ]);
    }

    /** @test */
    public function it_counts_the_number_of_times_a_user_accesses_to_an_oem_and_store_the_oem_search_counter_id()
    {
        $layout           = Layout::factory()->create();
        $oem              = Oem::factory()->create();
        $route            = URL::route($this->routeName, [RouteParameters::OEM => $oem]);
        $user             = User::factory()->create();
        $oemSearchCounter = OemSearchCounter::factory()->usingUser($user)->create();

        $this->login($user);
        $response = $this->getWithParameters($route, [
            RequestKeys::VERSION            => $layout->version,
            RequestKeys::OEM_SEARCH_COUNTER => $oemSearchCounter->uuid,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $oem->getKey(),
            'user_id'               => $user->getKey(),
            'oem_search_counter_id' => $oemSearchCounter->getKey(),
        ]);
    }
}
