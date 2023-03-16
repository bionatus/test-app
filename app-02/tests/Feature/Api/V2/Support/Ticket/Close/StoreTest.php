<?php

namespace Tests\Feature\Api\V2\Support\Ticket\Close;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\Support\Ticket\CloseController;
use App\Http\Resources\Api\V2\Support\Ticket\Close\BaseResource;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CloseController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_SUPPORT_TICKET_CLOSE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, Ticket::factory()->create()));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:close,' . RouteParameters::TICKET]);
    }

    /** @test */
    public function it_closes_an_open_ticket()
    {
        $ticket = Ticket::factory()->open()->create();
        $this->login($ticket->user);
        $response = $this->post(URL::route($this->routeName, $ticket->getRouteKey()));
        $ticket->refresh();

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
        $data = Collection::make($response->json('data'));

        $this->assertSame($ticket->getRouteKey(), $data->get('id'));
        $this->assertTrue($data->get('closed'));
        $this->assertNotNull($ticket->closed_at);
    }

    /** @test */
    public function it_does_not_change_its_close_status_on_a_closed_ticket()
    {
        $ticket = Ticket::factory()->closed()->create();

        $this->login($ticket->user);
        $response = $this->post(URL::route($this->routeName, $ticket->getRouteKey()));

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
        $data = Collection::make($response->json('data'));
        $this->assertSame($ticket->getRouteKey(), $data->get('id'));
        $this->assertTrue($data->get('closed'));
        $this->assertEquals($ticket->closed_at, $ticket->refresh()->closed_at);
    }
}
