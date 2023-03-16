<?php

namespace Tests\Feature\Api\V2\Support\Ticket\Rate;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\Support\Ticket\RateController;
use App\Http\Requests\Api\V2\Support\Ticket\Rate\StoreRequest;
use App\Http\Resources\Api\V2\Support\Ticket\Rate\BaseResource;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see RateController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_SUPPORT_TICKET_RATE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:rate,' . RouteParameters::TICKET]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /**
     * @test
     *
     * @param int         $rating
     * @param string|null $comment
     *
     * @dataProvider rateProvider
     */
    public function it_rates_a_ticket(int $rating, ?string $comment)
    {
        $ticket = Ticket::factory()->closed()->create();
        $this->login($ticket->user);
        $response = $this->post(URL::route($this->routeName, $ticket), [
            RequestKeys::RATING  => $rating,
            RequestKeys::COMMENT => $comment,
        ]);
        $ticket->refresh();

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $data = Collection::make($response->json('data'));

        $this->assertSame($ticket->getRouteKey(), $data->get('id'));
        $this->assertSame($ticket->rating, $data->get('rating'));
        $this->assertSame($ticket->comment, $data->get('comment'));
    }

    public function rateProvider(): array
    {
        return [
            [2, 'a valid comment'],
            [2, null],
        ];
    }
}
