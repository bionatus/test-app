<?php

namespace Tests\Feature\LiveApi\V1\Part;

use App;
use App\Actions\Models\IncrementPartSearches;
use App\Actions\Models\Part\SearchCharacterProximity;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\PartController;
use App\Http\Requests\LiveApi\V1\Part\IndexRequest;
use App\Http\Resources\LiveApi\V1\Part\BaseResource;
use App\Models\Part;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use Auth;
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

    private string $routeName = RouteNames::LIVE_API_V1_PART_INDEX;

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
    public function it_displays_a_part_list_filtered_by_number_and_sorted_by_functional_parts_and_oldest_model_key()
    {
        $expectedParts = Collection::make([]);
        $expectedParts->add(Part::factory()->number('12ae11')->other()->create());
        $expectedParts->prepend(Part::factory()->number('34ae11')->create());
        $expectedParts->add(Part::factory()->number('98ae11ab')->other()->create());
        $expectedParts->splice(1, 0, [Part::factory()->number('34ae11')->create()]);
        $numbersWithoutCoincidence = Collection::make([
            '6c8bee',
            '8a0f9b',
            '30b436',
        ]);
        $numbersWithoutCoincidence->each(function($number) {
            Part::factory()->number($number)->create();
        });

        $search = 'ae11';
        $route  = URL::route($this->routeName, [RequestKeys::NUMBER => $search]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
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
    public function it_stores_a_part_search_log()
    {
        $parts = Part::factory()->number($partNumber = 'a part number')->count(3)->create();
        Part::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::NUMBER => $partNumber]);

        Auth::shouldUse('live');
        $this->login($staff = Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $meta = $response->json('meta');

        $this->assertDatabaseCount(PartSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(PartSearchCounter::tableName(), [
            'uuid'     => $meta['part_search_counter_id'],
            'staff_id' => $staff->getKey(),
            'criteria' => $partNumber,
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
        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());

        $items   = Collection::make($parts);
        $page    = 1;
        $perPage = 15;

        $pagination = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page);

        $actionSearchCharacterProximity = Mockery::mock(SearchCharacterProximity::class);
        $actionSearchCharacterProximity->shouldReceive('execute')->withAnyArgs()->once()->andReturn([$pagination, 3]);
        App::bind(SearchCharacterProximity::class, fn() => $actionSearchCharacterProximity);

        $partSearchCounter = Mockery::mock(PartSearchCounter::class);
        $partSearchCounter->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('search uuid');
        $actionIncrementPartSearches = Mockery::mock(IncrementPartSearches::class);
        $actionIncrementPartSearches->shouldReceive('execute')->withAnyArgs()->once()->andReturn($partSearchCounter);
        App::bind(IncrementPartSearches::class, fn() => $actionIncrementPartSearches);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
    }
}
