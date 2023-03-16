<?php

namespace Tests\Unit\Listeners\Order;

use App\Events\Order\Canceled;
use App\Listeners\Order\RemovePointsOnCanceled;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Order;
use App\Models\Point;
use App\Models\Scopes\ByRouteKey;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\LevelsSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use ReflectionClass;
use Tests\TestCase;

class RemovePointsOnCanceledTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemovePointsOnCanceled::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_removes_points_related_to_an_order_and_setting_correct_level()
    {
        $user  = User::factory()->create();
        $order = Order::factory()->usingUser($user)->canceled()->createQuietly();

        $seederLevel = new LevelsSeeder();
        $seederLevel->run();

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => $multiplier = 5,
        ]);
        $coefficient = $user->currentLevel()->coefficient;
        Point::factory()->usingOrder($order)->create([
            'points_earned' => $pointsEarned = 90,
            'multiplier'    => $multiplier,
            'coefficient'   => $coefficient,
        ]);

        $event    = new Canceled($order);
        $listener = App::make(RemovePointsOnCanceled::class);
        $listener->handle($event);

        $this->assertDatabaseHas(Point::tableName(), [
            'object_type'   => Order::MORPH_ALIAS,
            'object_id'     => $order->getKey(),
            'action'        => Point::ACTION_ORDER_CANCELED,
            'coefficient'   => $coefficient,
            'multiplier'    => $multiplier,
            'points_earned' => $pointsEarned * -1,
        ]);

        $level0 = Level::scoped(new ByRouteKey(Level::SLUG_LEVEL_0))->first();

        $this->assertSame($level0->getKey(), $user->currentLevel()->getKey());
    }

    /** @test */
    public function it_does_not_create_points_if_the_order_has_not_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->canceled()->create(['user_id' => null]);
        $event    = new Canceled($order);
        $listener = App::make(RemovePointsOnCanceled::class);
        $listener->handle($event);

        $this->assertDatabaseMissing(Point::tableName(), [
            'object_type' => Order::MORPH_ALIAS,
            'object_id'   => $order->getKey(),
            'action'      => Point::ACTION_ORDER_CANCELED,
        ]);
    }
}
