<?php

namespace Tests\Feature\Api\V2\Agent;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\AgentController;
use App\Http\Resources\Api\V2\Agent\BaseResource;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see AgentController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_AGENT_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_display_a_list_of_agents()
    {
        $agents = Agent::factory()->count(100)->create();
        $route  = URL::route($this->routeName);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $agents);

        $data = Collection::make($response->json('data'));

        $firstPageAgents = $agents->sortBy('id')->values()->take(count($data));

        $data->each(function (array $rawAgent, int $index) use ($firstPageAgents) {
            $agent = $firstPageAgents->get($index);
            $this->assertSame($agent->getRouteKey(), $rawAgent['id']);
        });
    }
}
