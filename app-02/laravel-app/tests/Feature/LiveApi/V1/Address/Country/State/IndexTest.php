<?php

namespace Tests\Feature\LiveApi\V1\Address\Country\State;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Address\Country\StateController;
use App\Http\Requests\LiveApi\V1\Address\Country\State\IndexRequest;
use App\Http\Resources\Types\StateResource;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use MenaraSolutions\Geographer\Earth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see StateController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ADDRESS_COUNTRY_STATE_INDEX;

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

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
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
