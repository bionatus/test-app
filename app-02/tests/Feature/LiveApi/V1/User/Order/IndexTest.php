<?php

namespace Tests\Feature\LiveApi\V1\User\Order;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\User\OrderController;
use App\Http\Resources\LiveApi\V1\User\Order\BaseResource;
use App\Http\Resources\Models\Brand\ImageResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Series;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;
    use WithFaker;

    private string $routeName = RouteNames::LIVE_API_V1_USER_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $user = User::factory()->create();
        $this->get(URL::route($this->routeName, $user));
    }

    /** @test */
    public function it_display_an_user_order_list_oldest_first_and_filtered_by_status()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $user     = User::factory()->create();
        $now      = Carbon::now();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, $user);

        Order::factory()->usingSupplier($supplier)->count(7)->create();

        $orderList = Collection::make([
            Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create(),
            Order::factory()->canceled()->usingSupplier($supplier)->usingUser($user)->create(),
            Order::factory()->completed()->usingSupplier($supplier)->usingUser($user)->create(),
            Order::factory()
                ->pendingApproval()
                ->usingSupplier($supplier)
                ->usingUser($user)
                ->create(['created_at' => $now->clone()->subMinutes(50)]),
            Order::factory()->pending()->usingSupplier($supplier)->usingUser($user)->create([
                'created_at' => $now->clone()->subMinutes(30),
            ]),
            Order::factory()->pending()->usingSupplier($supplier)->usingUser($user)->create([
                'created_at' => $now->clone()->subMinutes(40),
            ]),
            Order::factory()->pending()->usingSupplier($supplier)->usingUser($user)->create([
                'created_at' => $now->clone()->subMinutes(10),
            ]),
            Order::factory()->pending()->usingSupplier($supplier)->usingUser($user)->create([
                'created_at' => $now->clone()->subMinutes(20),
            ]),
        ])
            ->whereIn('lastStatus.substatus_id',
                [Substatus::STATUS_PENDING_REQUESTED, Substatus::STATUS_PENDING_APPROVAL_FULFILLED])
            ->sortByDesc('created_at');
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
       
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $orderList);

        $data            = Collection::make($response->json('data'));
        $firstPageOrders = $orderList->values()->take(count($data));

        $this->assertEquals($firstPageOrders->pluck(Order::routeKeyName()), $data->pluck(Order::keyName()));
    }

    /** @test */
    public function it_displays_brand_image_related_orders()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $user     = User::factory()->create();
        Auth::shouldUse('live');
        $this->login($staff);

        $route      = URL::route($this->routeName, $user);
        $brand      = Brand::factory()->logo()->create();
        $brandImage = new ImageResource($brand->logo[0]);
        $series     = Series::factory()->usingBrand($brand)->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(7)->create();

        $oem       = Oem::factory()->usingSeries($series)->create();
        $orderList = Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->usingOem($oem)
            ->pending()
            ->count(10)
            ->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $orderList);

        $data = Collection::make($response->json('data'));
        $data->each(function(array $rawOrder) use ($brandImage) {
            $this->assertSame($rawOrder['oem']['series']['brand']['image']['url'], $brandImage['url']);
        });
    }
}
