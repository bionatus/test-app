<?php

namespace Tests\Feature\LiveApi\V1\Supplier\BulkHour;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Supplier\BulkHourController;
use App\Http\Resources\LiveApi\V1\Supplier\BulkHour\BaseResource;
use App\Models\Staff;
use App\Models\SupplierHour;
use Arr;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;
use App\Http\Requests\LiveApi\V1\Supplier\BulkHour\StoreRequest;

/** @see BulkHourController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_SUPPLIER_BULK_HOUR_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_create_branch_hours()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName);

        $branchHours = Collection::make([
            ['day' => 'monday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'tuesday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'wednesday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'thursday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'friday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'saturday', 'from' => '09:30', 'to' => '17:00'],
        ]);

        $response = $this->post($route, [RequestKeys::HOURS => $branchHours->toArray()]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseCount(SupplierHour::tableName(), count($branchHours));

        $branchHours->each(function($branchHour) use ($supplier) {
            $branchHour['supplier_id'] = $supplier->getKey();
            $branchHour['from']        = Carbon::create($branchHour['from'])->format('g:i a');
            $branchHour['to']          = Carbon::create($branchHour['to'])->format('g:i a');
            $this->assertDatabaseHas(SupplierHour::tableName(), $branchHour);
        });

        $data = Collection::make($response->json('data'));
        $data->each(function($rawBranchHour) use ($branchHours) {
            $expected = $branchHours->keyBy('day')->get($rawBranchHour['day']);
            $this->assertEquals($expected, $rawBranchHour);
        });
    }

    /** @test */
    public function it_syncs_branch_hours()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        SupplierHour::factory()->monday()->usingSupplier($supplier)->create();
        SupplierHour::factory()->wednesday()->usingSupplier($supplier)->create();
        $oldFridayBranchHour = SupplierHour::factory()->friday()->usingSupplier($supplier)->create();

        $route = URL::route($this->routeName);

        $branchHours = Collection::make([
            ['day' => 'monday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'tuesday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'wednesday', 'from' => '09:00', 'to' => '17:00'],
            ['day' => 'saturday', 'from' => '09:30', 'to' => '13:30'],
        ]);

        $response = $this->post($route, [RequestKeys::HOURS => $branchHours->toArray()]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $this->assertDeleted($oldFridayBranchHour);

        $this->assertDatabaseCount(SupplierHour::tableName(), count($branchHours));

        $data = Collection::make($response->json('data'));
        $data->each(function($rawBranchHour) use ($branchHours) {
            $expected = $branchHours->keyBy('day')->get($rawBranchHour['day']);
            $this->assertEquals($expected, $rawBranchHour);
        });
    }

    /** @test */
    public function it_removes_extra_branch_hours()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        $oldMondayBranchHour    = SupplierHour::factory()->monday()->usingSupplier($supplier)->create();
        $oldWednesdayBranchHour = SupplierHour::factory()->wednesday()->usingSupplier($supplier)->create();
        $fridayBranchHour       = SupplierHour::factory()->friday()->usingSupplier($supplier)->create();

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::HOURS => [
                $rawFridayBranchHour = Arr::only($fridayBranchHour->toArray(), ['day', 'from', 'to']),
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $this->assertDeleted($oldMondayBranchHour);
        $this->assertDeleted($oldWednesdayBranchHour);

        $this->assertDatabaseCount(SupplierHour::tableName(), 1);

        $data = Collection::make($response->json('data'));

        $this->assertEquals($rawFridayBranchHour, $data->first());
    }

    /** @test */
    public function it_removes_all_branch_hours_if_nothing_is_sent()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        $mondayBranchHour    = SupplierHour::factory()->monday()->usingSupplier($supplier)->create();
        $wednesdayBranchHour = SupplierHour::factory()->wednesday()->usingSupplier($supplier)->create();
        $fridayBranchHour    = SupplierHour::factory()->friday()->usingSupplier($supplier)->create();

        $route = URL::route($this->routeName);

        $response = $this->post($route, [RequestKeys::HOURS => '']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $this->assertDeleted($mondayBranchHour);
        $this->assertDeleted($wednesdayBranchHour);
        $this->assertDeleted($fridayBranchHour);

        $this->assertDatabaseCount(SupplierHour::tableName(), 0);
    }
}
