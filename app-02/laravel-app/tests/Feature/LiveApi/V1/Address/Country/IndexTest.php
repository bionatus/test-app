<?php

namespace Tests\Feature\LiveApi\V1\Address\Country;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Address\CountryController;
use App\Http\Requests\LiveApi\V1\Address\Country\IndexRequest;
use App\Http\Resources\Types\CountryResource;
use App\Models\Staff;
use Auth;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use MenaraSolutions\Geographer\Earth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CountryController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ADDRESS_COUNTRY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_list_of_countries_sorted_alphabetically()
    {
        Config::set('communications.allowed_countries', $allowedCountries = ['US', 'CA', 'MX', 'AU', 'AR']);
        $geo          = new Earth();
        $rawCountries = $geo->getCountries()->useShortNames()->sortBy('name');
        $countries    = Collection::make($rawCountries)->filter(function($country) use ($allowedCountries) {
            return in_array($country->code, $allowedCountries);
        })->values();

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->collectionSchema(CountryResource::jsonSchema()), $response);
        $this->assertCount(count($response->json('data')), $countries);

        $data = Collection::make($response->json('data'));
        $data->each(function(array $rawCountry, int $index) use ($countries) {
            $country = $countries->get($index);
            $this->assertSame($country->getCode(), $rawCountry['code']);
        });
    }
}
