<?php

namespace Tests\Feature\LiveApi\V1\Oem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\OemController;
use App\Http\Resources\LiveApi\V1\Oem\DetailedResource;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OemController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_OEM_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, Oem::factory()->create()));
    }

    /** @test */
    public function it_displays_an_oem()
    {
        $oem   = Oem::factory()->create();
        $route = URL::route($this->routeName, $oem);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $oem->getRouteKey());
    }

    /** @test */
    public function it_counts_the_number_of_times_a_staff_accesses_to_an_oem()
    {
        $oem   = Oem::factory()->create();
        $route = URL::route($this->routeName, $oem);
        $staff = Staff::factory()->createQuietly();

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $oem->getKey(),
            'staff_id'              => $staff->getKey(),
            'oem_search_counter_id' => null,
        ]);
    }

    /** @test */
    public function it_counts_the_number_of_times_a_staff_accesses_to_an_oem_and_store_the_oem_search_counter_id()
    {
        $oem              = Oem::factory()->create();
        $route            = URL::route($this->routeName, [RouteParameters::OEM => $oem]);
        $staff            = Staff::factory()->createQuietly();
        $oemSearchCounter = OemSearchCounter::factory()->usingStaff($staff)->create();

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->getWithParameters($route, [
            RequestKeys::OEM_SEARCH_COUNTER => $oemSearchCounter->uuid,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $oem->getKey(),
            'staff_id'              => $staff->getKey(),
            'oem_search_counter_id' => $oemSearchCounter->getKey(),
        ]);
    }
}
