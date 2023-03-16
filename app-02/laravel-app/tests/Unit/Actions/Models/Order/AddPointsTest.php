<?php

namespace Tests\Unit\Actions\Models\Order;

use App;
use App\Actions\Models\Order\AddPoints;
use App\Actions\Models\Order\CalculatePoints;
use App\Events\Order\PointsEarned;
use App\Models\AppSetting;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Point as PointModel;
use App\Models\Supplier;
use App\Models\User;
use App\Types\Point;
use Database\Seeders\LevelsSeeder;
use Event;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Tests\TestCase;
use Throwable;

class AddPointsTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    /** @test
     * @dataProvider dataProvider
     * @throws Throwable
     */
    public function it_creates_points_with_multiplier_and_setting_correct_level(string $action)
    {
        Event::fake(PointsEarned::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('processLevel')->once()->withNoArgs();
        $pointsRelationship = Mockery::mock(HasMany::class);
        $user->shouldReceive('points')->once()->withNoArgs()->andReturn($pointsRelationship);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->once()->with('user')->andReturn($user);
        $order->shouldReceive('getKey')->once()->withNoArgs()->andReturn($orderId = 99);

        $pointsType = new Point($points = 100, $coefficient = 0.5, $multiplier = 2);

        $calculatePointsAction = Mockery::mock(CalculatePoints::class);
        $calculatePointsAction->shouldReceive('execute')->once()->andReturn($pointsType);
        App::bind(CalculatePoints::class, fn() => $calculatePointsAction);

        $attributes = [
            'object_type'   => 'order',
            'object_id'     => $orderId,
            'action'        => $action,
            'coefficient'   => $coefficient,
            'multiplier'    => $multiplier,
            'points_earned' => $points,
        ];
        $pointsRelationship->shouldReceive('create')->once()->with($attributes);

        (new AddPoints($order, $action))->execute();
    }

    /** @test
     * @dataProvider dataProvider
     * @throws Throwable
     */
    public function it_throws_an_exception_if_the_order_has_not_user(string $action)
    {
        $order = Mockery::mock(Order::class);

        $order->shouldReceive('getAttribute')->once()->with('user')->andReturnNull();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not add points to order with deleted users');

        (new AddPoints($order, $action))->execute();
    }

    /** @test
     * @dataProvider dataProvider
     * @throws Throwable
     */
    public function it_dispatches_an_points_earned_event(string $action)
    {
        Event::fake(PointsEarned::class);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->available()->create();

        (new LevelsSeeder())->run();

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        (new AddPoints($order, $action))->execute();

        Event::assertDispatched(PointsEarned::class);
    }

    public function dataProvider(): array
    {
        return [
            [PointModel::ACTION_ORDER_APPROVED],
            [PointModel::ACTION_ORDER_COMPLETED],
        ];
    }
}
