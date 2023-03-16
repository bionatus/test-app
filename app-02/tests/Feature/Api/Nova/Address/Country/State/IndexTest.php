<?php

namespace Tests\Feature\Api\Nova\Address\Country\State;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\Nova\Address\Country\StateController;
use App\Http\Requests\Api\Nova\Address\Country\State\IndexRequest;
use App\Http\Resources\Api\Nova\Address\Country\State\StateResource;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Nova\Exceptions\AuthenticationException;
use MenaraSolutions\Geographer\Earth;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\Nova\TestCase;
use URL;

/** @see StateController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string  $routeName = RouteNames::API_NOVA_ADDRESS_COUNTRY_STATE_INDEX;
    private ?string $wrap;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wrap = StateResource::$wrap;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        StateResource::wrap($this->wrap);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->get(URL::route($this->routeName, [RouteParameters::COUNTRY => 'US']));
    }

    /** @test */
    public function an_unauthorized_user_can_not_proceed()
    {
        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();

        $this->actingAs($user);

        $response = $this->get(URL::route($this->routeName, [RouteParameters::COUNTRY => 'US']));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_list_of_states_sorted_alphabetically()
    {
        $this->login();

        $geo       = new Earth();
        $rawStates = $geo->findOneByCode('US')->getStates()->useShortNames()->sortBy('name');
        $states    = Collection::make($rawStates)->values();

        $response = $this->get(URL::route($this->routeName, [RouteParameters::COUNTRY => 'US']));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->collectionSchema(StateResource::jsonSchema(), false), $response);
        $this->assertCount(count($response->json()), $states);

        $data = Collection::make($response->json());
        $data->each(function(array $rawState, int $index) use ($states) {
            $state = $states->get($index);
            $this->assertSame($state->getIsoCode(), $rawState['value']);
        });
    }
}
