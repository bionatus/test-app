<?php

namespace Actions\Models\Supplier;

use App\Actions\Models\Supplier\GetNextWorkingDays;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Types\SupplierWorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GetNextWorkingDaysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_next_working_hours_from_supplier_with_no_hours_available()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $nextWorkingHours = new Collection();
        $this->assertEquals($nextWorkingHours, (new GetNextWorkingDays($supplier))->execute());
    }

    /** @test */
    public function it_gets_next_working_hours_from_supplier_with_hours_available()
    {
        Carbon::setTestNow('2023-02-06 00:00:01');
        $supplier = Supplier::factory()->createQuietly();
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => 'monday',
            'from' => '09:00 am',
            'to'   => '05:00 pm',
        ]);
        $nextWorkingHours = Collection::make([]);
        for ($i = 0; $i < 21; $i++) {
            $today = Carbon::now()->addDays($i * 7);
            $nextWorkingHours->push(new SupplierWorkingHour($today->day . '/' . $today->month . '/' . $today->year,
                '09:00 am', '05:00 pm', $supplier->timezone));
        }
        $this->assertEquals($nextWorkingHours, (new GetNextWorkingDays($supplier))->execute());
    }
}
