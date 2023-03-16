<?php

namespace Tests\Feature\LiveApi\V2\Supplier\Staff;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Supplier\StaffController;
use App\Http\Requests\LiveApi\V2\Supplier\Staff\IndexRequest;
use App\Http\Resources\LiveApi\V2\Supplier\Staff\BaseResource;
use App\Models\OrderStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see StaffController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_SUPPLIER_STAFF_INDEX;

    protected function setUp(): void
    {
        parent::setUp();
        $this->maxItems = 5;
        $this->supplier = Supplier::factory()->createQuietly();
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]);
        $this->get($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_staff_list_with_no_filter_type_ordered_by_oldest_creation()
    {
        $expectedStaff = Collection::make([]);
        $currentStaff  = null;
        for ($i = 1; $i < $this->maxItems; $i++) {
            $currentStaff = Staff::factory()->usingSupplier($this->supplier)->create([
                'created_at' => "2022-12-0$i 10:00:00",
            ]);
            $expectedStaff->add($currentStaff);
        }
        $route = URL::route($this->routeName, [RequestKeys::SUPPLIER => $this->supplier->getRouteKey()]);
        Auth::shouldUse('live');
        $this->login($currentStaff);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);
        $data = Collection::make($response->json('data'));
        $this->assertCount($response->json('meta.total'), $expectedStaff);
        $data->each(function(array $rawOrder, int $index) use ($expectedStaff) {
            $staff = $expectedStaff->get($index);
            $this->assertSame($staff->getRouteKey(), $rawOrder['id']);
        });
    }

    /** @test */
    public function it_displays_a_staff_list_filtered_by_staff_type_ordered_by_oldest_creation()
    {
        $expectedStaff     = Collection::make([]);
        $selectedStaffType = Staff::TYPE_OWNER;
        $currentStaff      = null;
        foreach (Staff::STAFF_TYPES as $staffType) {
            for ($i = 1; $i < $this->maxItems; $i++) {
                $currentStaff = Staff::factory()->usingSupplier($this->supplier)->create([
                    'type'       => $staffType,
                    'created_at' => "2022-12-0$i 10:00:00",
                ]);
                if ($selectedStaffType === $staffType) {
                    $expectedStaff->add($currentStaff);
                }
            }
        }
        $route = URL::route($this->routeName, [
            RequestKeys::SUPPLIER => $this->supplier->getRouteKey(),
            RequestKeys::TYPE     => $selectedStaffType,
        ]);
        Auth::shouldUse('live');
        $this->login($currentStaff);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);
        $data = Collection::make($response->json('data'));
        $this->assertCount($response->json('meta.total'), $expectedStaff);
        $data->each(function(array $rawOrder, int $index) use ($expectedStaff) {
            $staff = $expectedStaff->get($index);
            $this->assertSame($staff->getRouteKey(), $rawOrder['id']);
        });
    }

    /** @test */
    public function it_displays_a_staff_list_filtered_by_staff_type_ordered_by_oldest_creation_with_last_assigned_staff_first(
    )
    {
        $selectedStaff     = Collection::make([]);
        $selectedStaffType = Staff::TYPE_COUNTER;
        foreach (Staff::STAFF_TYPES as $staffType) {
            $staffCollection = Staff::factory()->usingSupplier($this->supplier)->count($this->maxItems)->create([
                'type' => $staffType,
            ]);
            if ($staffType === $selectedStaffType) {
                $selectedStaff = $staffCollection;
            }
        }

        $firstStaff = Staff::factory()->usingSupplier($this->supplier)->create(['type' => $selectedStaffType]);
        OrderStaff::factory()->usingStaff($firstStaff)->create();
        $selectedStaff->prepend($firstStaff);

        $route = URL::route($this->routeName,
            [RequestKeys::SUPPLIER => $this->supplier->getRouteKey(), RequestKeys::TYPE => $selectedStaffType]);
        Auth::shouldUse('live');
        $this->login($this->supplier->staff()->first());
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);
        $data = Collection::make($response->json('data'));
        $this->assertCount($response->json('meta.total'), $selectedStaff);
        $data->each(function(array $rawOrder, int $index) use ($selectedStaff) {
            $staff = $selectedStaff->get($index);
            $this->assertSame($staff->getRouteKey(), $rawOrder['id']);
        });
    }
}
