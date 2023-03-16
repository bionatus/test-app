<?php

namespace Tests\Feature\Api\V3\Order;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Resources\Api\V3\Order\DetailedResource;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\PendingApprovalView;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\LevelsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OrderController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ORDER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_displays_an_order()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $seederLevel = new LevelsSeeder();
        $seederLevel->run();

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $seederLevel = new LevelsSeeder();
        $seederLevel->run();

        $this->login($user);
        $route = URL::route($this->routeName, $order);

        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertSame($data['id'], $order->getRouteKey());
    }

    /** @test
     * @dataProvider logPendingApprovalFirstViewProvider
     */
    public function it_logs_the_view_if_requirements_are_met(
        int $substatusId,
        bool $pendingApprovalViewExist,
        bool $expected
    ) {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $seederLevel = new LevelsSeeder();
        $seederLevel->run();

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        $seederLevel = new LevelsSeeder();
        $seederLevel->run();

        if ($pendingApprovalViewExist) {
            PendingApprovalView::factory()->usingOrder($order)->create();
        }

        $this->login($user);
        $route = URL::route($this->routeName, $order);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);

        if ($expected) {
            $this->assertDatabaseHas(PendingApprovalView::tableName(), [
                'order_id' => $order->getKey(),
                'user_id'  => $user->getKey(),
            ]);
        } else {
            $this->assertDatabaseMissing(PendingApprovalView::tableName(), [
                'order_id' => $order->getKey(),
                'user_id'  => $user->getKey(),
            ]);
        }
    }

    public function logPendingApprovalFirstViewProvider(): array
    {
        return [
            //status, pendingApprovalViewExist, expected
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true, false],
            [Substatus::STATUS_CANCELED_REJECTED, false, false],
            [Substatus::STATUS_CANCELED_REJECTED, true, false],
            [Substatus::STATUS_COMPLETED_DONE, false, false],
            [Substatus::STATUS_COMPLETED_DONE, true, false],
            [Substatus::STATUS_PENDING_REQUESTED, false, false],
            [Substatus::STATUS_PENDING_REQUESTED, true, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true, false],
        ];
    }
}

