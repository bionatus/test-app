<?php

namespace Tests\Feature\LiveApi\V2\Part;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\PartController;
use App\Http\Resources\LiveApi\V2\Part\DetailedResource;
use App\Models\AirFilter;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PartController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = LiveApiV2::LIVE_API_V2_PART_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $airFilter = AirFilter::factory()->create();
        $item      = $airFilter->part->item;
        $route     = URL::route($this->routeName, [RouteParameters::PART => $item]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_displays_a_part()
    {
        $airFilter = AirFilter::factory()->create();
        $item      = $airFilter->part->item;
        $route     = URL::route($this->routeName, [RouteParameters::PART => $item]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $item->getRouteKey());
    }

    /** @test */
    public function it_counts_the_number_of_times_a_staff_accesses_to_a_part()
    {
        $airFilter = AirFilter::factory()->create();
        $item      = $airFilter->part->item;
        $route     = URL::route($this->routeName, [RouteParameters::PART => $item]);
        $staff     = Staff::factory()->createQuietly();

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(PartDetailCounter::tableName(), [
            'part_id'                => $airFilter->part->getKey(),
            'staff_id'               => $staff->getKey(),
            'part_search_counter_id' => null,
        ]);
    }

    /** @test */
    public function it_counts_the_number_of_times_a_user_accesses_to_a_part_and_store_the_part_search_counter_id()
    {
        $airFilter         = AirFilter::factory()->create();
        $item              = $airFilter->part->item;
        $staff             = Staff::factory()->createQuietly();
        $route             = URL::route($this->routeName, [RouteParameters::PART => $item]);
        $partSearchCounter = PartSearchCounter::factory()->usingStaff($staff)->create();

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->getWithParameters($route, [
            RequestKeys::PART_SEARCH_COUNTER => $partSearchCounter->uuid,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseCount(PartDetailCounter::tableName(), 1);
        $this->assertDatabaseHas(PartDetailCounter::tableName(), [
            'part_id'                => $airFilter->part->getKey(),
            'staff_id'               => $staff->getKey(),
            'part_search_counter_id' => $partSearchCounter->getKey(),
        ]);
    }
}
