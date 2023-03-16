<?php

namespace Tests\Unit\Actions\Models\Order;

use App\Actions\Models\Order\CalculatePoints;
use App\Models\AppSetting;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Database\Seeders\LevelsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

class CalculatePointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws Throwable
     */
    public function it_returns_the_correct_total_order_points_with_the_coefficient_and_the_multiplier_when_the_order_is_approved(
    )
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->available()->create([
            'quantity' => 2,
            'price'    => 10,
        ]);
        $seederLevel = new LevelsSeeder();
        $seederLevel->run();
        $coefficient = (float) $order->user->currentLevel()->coefficient;

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => $multiplier = 2,
        ]);

        $action = new CalculatePoints($order);
        $result = $action->execute();

        $this->assertSame(20, $result->points());
        $this->assertSame($coefficient, $result->coefficient());
        $this->assertSame($multiplier, $result->multiplier());
    }

    /** @test
     * @throws Throwable
     */
    public function it_returns_the_correct_total_order_points_with_the_coefficient_and_the_multiplier_when_the_order_is_completed(
    )
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->completed()->create(['total' => 100]);
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        ItemOrder::factory()->usingOrder($order)->available()->create([
            'quantity' => 2,
            'price'    => 10,
        ]);
        $seederLevel = new LevelsSeeder();
        $seederLevel->run();
        $coefficient = (float) $order->user->currentLevel()->coefficient;

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => $multiplier = 2,
        ]);

        $action = new CalculatePoints($order);
        $result = $action->execute();

        $this->assertSame(100, $result->points());
        $this->assertSame($coefficient, $result->coefficient());
        $this->assertSame($multiplier, $result->multiplier());
    }
}
