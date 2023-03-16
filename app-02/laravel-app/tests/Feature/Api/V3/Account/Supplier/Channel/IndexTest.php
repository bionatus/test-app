<?php

namespace Tests\Feature\Api\V3\Account\Supplier\Channel;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Supplier\ChannelController;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ChannelController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_SUPPLIER_CHANNEL_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_that_has_orders_with_the_user_ordered_by_order_updated_at()
    {
        $now    = Carbon::now();
        $user   = User::factory()->create();
        $order1 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(40)]);
        $order2 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(30)]);
        $order3 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(10)]);
        $order4 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(20)]);
        Order::factory()->usingSupplier($order1->supplier)->count(2)->createQuietly([
            'updated_at' => $now->clone()
                ->subSeconds(5),
        ]);
        Order::factory()
            ->usingSupplier($order1->supplier)
            ->usingUser($user)
            ->createQuietly(['updated_at' => $now->clone()->subSeconds(35)]);
        Order::factory()->count(10)->createQuietly();

        $expectedSuppliersRouteKeys = [
            $order3->supplier->getRouteKey(),
            $order4->supplier->getRouteKey(),
            $order2->supplier->getRouteKey(),
            $order1->supplier->getRouteKey(),
        ];

        $this->login($user);
        $response                  = $this->get(URL::route($this->routeName));
        $data                      = Collection::make($response->json('data'));
        $currentSuppliersRouteKeys = $data->pluck(Supplier::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->assertSame($expectedSuppliersRouteKeys, $currentSuppliersRouteKeys);
    }
}
