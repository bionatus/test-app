<?php

namespace Tests\Feature\LiveApi\V1\Oem;

use App;
use App\Actions\Models\IncrementOemSearches;
use App\Actions\Models\Oem\SearchCharacterProximity;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\OemController;
use App\Http\Requests\LiveApi\V1\Oem\IndexRequest;
use App\Http\Resources\LiveApi\V1\Oem\BaseResource;
use App\Models\Oem;
use App\Models\OemSearchCounter;
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

/** @see OemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_OEM_INDEX;

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
    public function it_display_a_list_of_published_oems_filtered_by_model_number()
    {
        $expectedOems = Collection::make([]);
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model b']));
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model c']));
        $expectedOems->prepend(Oem::factory()->create(['model' => 'oem model a']));

        Oem::factory()->count(2)->createQuietly(['status' => null, 'model' => 'oem model d']);
        Oem::factory()->count(2)->create();

        $search = 'oem model';
        $route  = URL::route($this->routeName, [RequestKeys::MODEL => $search]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawOem, int $index) use ($expectedOems) {
            $oem = $expectedOems->get($index);
            $this->assertEquals($oem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_published_oems_sorted_by_model_and_model_notes_alphabetically()
    {
        $expectedOems = Collection::make([]);
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model b']));
        $expectedOems->add(Oem::factory()->create(['model' => 'oem model c']));
        $expectedOems->prepend(Oem::factory()->create(['model' => 'oem model a', 'model_notes' => 'note c']));
        $expectedOems->prepend(Oem::factory()->create(['model' => 'oem model a', 'model_notes' => 'note b']));
        $expectedOems->prepend(Oem::factory()->create(['model' => 'oem model a', 'model_notes' => 'note a']));

        Oem::factory()->count(2)->createQuietly(['status' => null, 'model' => 'oem model d']);
        Oem::factory()->count(2)->create();

        $search = 'oem model';
        $route  = URL::route($this->routeName, [RequestKeys::MODEL => $search]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawOem, int $index) use ($expectedOems) {
            $oem = $expectedOems->get($index);
            $this->assertEquals($oem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_applies_sorting_correctly_on_each_page()
    {
        $oems = Oem::factory()->count(50)->create(['model' => 'RGDJ-10EBRGA']);

        $sortedOems = $oems->sortBy('model');

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());

        $expectedOems = $sortedOems->chunk(15, function() {
        });

        $alreadyReturned = Collection::make();
        $expectedOems->each(function(Collection $expectedOemsForPage, int $pageIndex) use ($alreadyReturned) {
            $response = $this->get(URL::route($this->routeName,
                [RequestKeys::PAGE => $page = $pageIndex + 1, RequestKeys::MODEL => 'BRG']));
            $response->assertStatus(Response::HTTP_OK);
            $data = Collection::make($response->json('data'));
            $data->each(function(array $rawOem) use ($page, $alreadyReturned, $expectedOemsForPage) {
                $this->assertNotContains($rawOem['id'], $alreadyReturned, "Repeated record on page $page");
            });

            $alreadyReturned->push(...$data->pluck('id'));
        });
    }

    /** @test */
    public function it_stores_an_oem_search_log()
    {
        $searchString = 'STR-TO-SEARCH';

        $oems = Oem::factory()->count(3)->create(['model' => 'STR-MODEL']);
        Oem::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::MODEL => $searchString]);

        Auth::shouldUse('live');
        $this->login($staff = Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $meta = $response->json('meta');

        $this->assertDatabaseCount(OemSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(OemSearchCounter::tableName(), [
            'uuid'     => $meta['oem_search_counter_id'],
            'staff_id' => $staff->getKey(),
            'criteria' => $searchString,
            'results'  => $oems->count(),
        ]);
    }

    /** @test */
    public function it_executes_actions_when_list_oems()
    {
        $searchString = 'STR-TO-SEARCH';

        $oems = Oem::factory()->count(3)->create(['model' => 'STR-MODEL']);
        Oem::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::MODEL => $searchString]);

        $items   = Collection::make($oems);
        $page    = 1;
        $perPage = 15;

        $pagination = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $this->get($route);

        $actionSearchCharacterProximity = Mockery::mock(SearchCharacterProximity::class);
        $actionSearchCharacterProximity->shouldReceive('execute')->withNoArgs()->once()->andReturn([$pagination, 3]);
        App::bind(SearchCharacterProximity::class, fn() => $actionSearchCharacterProximity);

        $oemSearchCounter = Mockery::mock(OemSearchCounter::class);
        $oemSearchCounter->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('search uuid');
        $actionIncrementOemSearches = Mockery::mock(IncrementOemSearches::class);
        $actionIncrementOemSearches->shouldReceive('execute')->withNoArgs()->once()->andReturn($oemSearchCounter);
        App::bind(IncrementOemSearches::class, fn() => $actionIncrementOemSearches);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
    }
}
