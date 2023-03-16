<?php

namespace Tests\Unit\Actions\Models\Order\Delivery;

use App\Actions\Models\Order\Delivery\CalculateJobExecutionTime;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CalculateJobExecutionTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider timeProvider
     */
    public function it_calculates_the_delay_time($dateNow, $date, $startTime, $endTime, $expected)
    {
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        $user     = User::factory()->create();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();
        Carbon::setTestNow($dateNow);

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $calculate = new CalculateJobExecutionTime($order);
        $delayTime = $calculate->execute();

        $expectedDate = Carbon::createFromFormat('Y-m-d H:i:s', $expected);
        $this->assertSame($expectedDate->timestamp, $delayTime->timestamp);
    }

    public function timeProvider()
    {
        return [
            [
                '2022-11-22 16:00:00',
                '2022-11-22',
                Carbon::createFromTime(9)->format('H:i'),
                Carbon::createFromTime(12)->format('H:i'),
                '2022-11-22 16:00:00',
            ],
            [
                '2022-11-22 14:00:00',
                '2022-11-22',
                Carbon::createFromTime(15)->format('H:i'),
                Carbon::createFromTime(17)->format('H:i'),
                '2022-11-22 14:30:00',
            ],
            [
                '2022-11-22 08:00:00',
                '2022-11-21',
                Carbon::createFromTime(9)->format('H:i'),
                Carbon::createFromTime(12)->format('H:i'),
                '2022-11-22 08:00:00',
            ],
            [
                '2022-11-22 16:00:00',
                '2022-11-23',
                Carbon::createFromTime(9)->format('H:i'),
                Carbon::createFromTime(12)->format('H:i'),
                '2022-11-23 08:30:00',
            ],
        ];
    }
}
