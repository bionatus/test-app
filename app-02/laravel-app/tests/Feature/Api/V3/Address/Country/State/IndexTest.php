<?php

namespace Tests\Feature\Api\V3\Address\Country\State;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Address\Country\StateController;
use App\Http\Requests\Api\V3\Address\Country\State\IndexRequest;
use App\Http\Resources\Types\StateResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Earth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see StateController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ADDRESS_COUNTRY_STATE_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, ['country' => 'US']));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_list_of_states_sorted_alphabetically()
    {
        $geo       = new Earth();
        $rawStates = $geo->findOneByCode('US')->getStates()->useShortNames()->sortBy('name');
        $states    = Collection::make($rawStates)->values();

        $this->login();
        $response = $this->get(URL::route($this->routeName, ['country' => 'US']));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->collectionSchema(StateResource::jsonSchema()), $response);
        $this->assertCount(count($response->json('data')), $states);

        $data = Collection::make($response->json('data'));
        $data->each(function(array $rawState, int $index) use ($states) {
            $state = $states->get($index);
            $this->assertSame($state->getIsoCode(), $rawState['code']);
        });
    }
}
