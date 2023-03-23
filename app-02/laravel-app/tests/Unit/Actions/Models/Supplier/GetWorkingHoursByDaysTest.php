<?php

namespace Tests\Unit\Actions\Models\Supplier;

use App\Actions\Models\Supplier\GetWorkingHoursByDays;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetWorkingHoursByDaysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_working_hours_by_day_from_supplier_with_no_hours_available()
    {
        $supplier          = Supplier::factory()->createQuietly();
        $workingHoursByDay = [
            'time_ranges' => [],
            'start_time'  => [],
            'end_time'    => [],
        ];
        $this->assertEquals($workingHoursByDay, (new GetWorkingHoursByDays($supplier))->execute());
    }

    /** @test */
    public function it_gets_working_hours_by_day_from_supplier_with_hours_available()
    {
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => 'monday',
            'from' => '7:45 pm',
            'to'   => '9:00 pm',
        ]);
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => 'tuesday',
            'from' => '02:00 pm',
            'to'   => '04:00 pm',
        ]);

        Carbon::setTestNow('2022-10-10 10:51:00AM'); //monday
        $date = Carbon::now()->format('Y-m-d H:i');

        $expected = [
            'time_ranges' => [
                [
                    'start' => '02:45',
                    'end'   => '03:45',
                ],
            ],
            'start_time'  => [
                '02:45' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-10 7:45 pm', 'America/Los_Angeles'),
            ],
            'end_time'    => [
                '03:45' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-10 8:45 pm', 'America/Los_Angeles'),
            ],
        ];

        $this->assertEquals($expected, (new GetWorkingHoursByDays($supplier, $date))->execute());
    }

    /** @test */
    public function it_gets_working_hours_by_day_from_supplier_when_day_rolls_out()
    {
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => 'monday',
            'from' => '10:00 pm',
            'to'   => '12:00 am',
        ]);

        $date = '2022-10-11 06:00';

        $expected = [
            'time_ranges' => [
                [
                    'start' => '05:00',
                    'end'   => '06:00',
                ],
                [
                    'start' => '06:00',
                    'end'   => '07:00',
                ],
            ],
            'start_time'  => [
                '05:00' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-10 10:00 pm', 'America/Los_Angeles'),
                '06:00' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-10 11:00 pm', 'America/Los_Angeles'),
            ],
            'end_time'    => [
                '06:00' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-10 11:00 pm', 'America/Los_Angeles'),
                '07:00' => Carbon::createFromFormat('Y-m-d G:i a', '2022-10-11 12:00 am', 'America/Los_Angeles'),
            ],
        ];

        $this->assertEquals($expected, (new GetWorkingHoursByDays($supplier, $date))->execute());
    }
}
