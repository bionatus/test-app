<?php

namespace Tests\Feature\Api\V3\Part;

use App;
use App\Actions\Models\IncrementPartSearches;
use App\Actions\Models\Part\SearchCharacterProximity;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\PartController;
use App\Http\Requests\Api\V3\Part\IndexRequest;
use App\Http\Resources\Api\V3\Part\BaseResource;
use App\Models\AirFilter;
use App\Models\Other;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PartController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_PART_INDEX;

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
    public function it_display_a_list_of_parts_filtered_by_number_part_functional_first()
    {
        $otherPart            = Part::factory()->number('partNumberB')->other()->create();
        $firstFunctionalPart  = Part::factory()->number('partNumberC')->create();
        $secondFunctionalPart = Part::factory()->number('partNumberA')->create();

        Other::factory()->usingPart($otherPart)->create();
        AirFilter::factory()->usingPart($firstFunctionalPart)->create();
        AirFilter::factory()->usingPart($secondFunctionalPart)->create();

        $expectedParts = Collection::make([$firstFunctionalPart, $secondFunctionalPart, $otherPart]);

        Part::factory()->count(2)->create();

        $search = 'partNumber';
        $route  = URL::route($this->routeName, [RequestKeys::NUMBER => $search]);
        $user   = User::factory()->create();

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawPart, int $index) use ($expectedParts) {
            $part = $expectedParts->get($index);
            $this->assertSame($part->item->getRouteKey(), $rawPart['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_parts_filtered_by_number_part_functional_first_and_ordered_by_most_viewed()
    {
        $fifthPart = Part::factory()->number('partNumberB')->other()->create();
        PartDetailCounter::factory()->usingPart($fifthPart)->count(2)->create();

        $firstPart = Part::factory()->number('partNumberC')->create();
        PartDetailCounter::factory()->usingPart($firstPart)->count(5)->create();

        $secondPart = Part::factory()->number('partNumberA')->create();
        PartDetailCounter::factory()->usingPart($secondPart)->count(3)->create();

        $thirdPart  = Part::factory()->number('partNumber')->create();
        $fourthPart = Part::factory()->number('partNumberD')->create();

        $expectedParts = Collection::make([
            $firstPart,
            $secondPart,
            $thirdPart,
            $fourthPart,
            $fifthPart,
        ]);

        $search = 'partNumber';
        $route  = URL::route($this->routeName, [RequestKeys::NUMBER => $search]);

        $user = User::factory()->create();
        $this->login($user);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(\App\Http\Resources\Api\V3\Oem\Part\BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawPart, int $index) use ($expectedParts) {
            $part = $expectedParts->get($index);
            $this->assertSame($part->item->getRouteKey(), $rawPart['id']);
        });
    }

    /** @test */
    public function it_stores_a_part_search_log()
    {
        $searchPartNumber = '4X0-123-456';

        $parts = Part::factory()->number('4X0-123141')->count(3)->create();
        Part::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::NUMBER => $searchPartNumber]);

        $this->login($user = User::factory()->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $meta = $response->json('meta');

        $this->assertDatabaseCount(PartSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(PartSearchCounter::tableName(), [
            'uuid'     => $meta['part_search_counter_id'],
            'user_id'  => $user->getKey(),
            'criteria' => $searchPartNumber,
            'results'  => $parts->count(),
        ]);
    }

    /** @test */
    public function it_executes_actions_when_list_parts()
    {
        $searchPartNumber = '4X0-123-456';

        $parts = Part::factory()->number('4X0-123141')->count(3)->create();
        Part::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::NUMBER => $searchPartNumber]);

        $items   = Collection::make($parts);
        $page    = 1;
        $perPage = 15;

        $pagination = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page);

        $this->login(User::factory()->create());
        $this->get($route);

        $actionSearchCharacterProximity = Mockery::mock(SearchCharacterProximity::class);
        $actionSearchCharacterProximity->shouldReceive('execute')->withNoArgs()->once()->andReturn([$pagination, 3]);
        App::bind(SearchCharacterProximity::class, fn() => $actionSearchCharacterProximity);

        $partSearchCounter = Mockery::mock(PartSearchCounter::class);
        $partSearchCounter->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('search uuid');
        $actionIncrementPartSearches = Mockery::mock(IncrementPartSearches::class);
        $actionIncrementPartSearches->shouldReceive('execute')->withNoArgs()->once()->andReturn($partSearchCounter);
        App::bind(IncrementPartSearches::class, fn() => $actionIncrementPartSearches);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
    }
}
