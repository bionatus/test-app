<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App;
use App\Actions\Models\Supplier\GetWorkingHoursByDays;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Rules\OrderDelivery\IsInEndWorkingHours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IsInEndWorkingHoursTest extends TestCase
{
    use RefreshDatabase;

    private array    $workingHours;
    private string   $date = '2023-02-10';
    private Supplier $supplier;

    /** @test */
    public function it_returns_false_if_time_provided_is_invalid()
    {
        $startTime = '09:00';
        Carbon::setTestNow($this->date . " $startTime");
        $endTime = 'not valid value';

        $rule = new IsInEndWorkingHours($this->workingHours);

        $this->assertFalse($rule->passes('attribute', $endTime));
    }

    /** @test */
    public function it_has_custom_message()
    {
        $startTime = '17:29';
        Carbon::setTestNow($this->date . " $startTime");

        $rule = new IsInEndWorkingHours($this->workingHours);

        $expectedMessage = "The selected requested end time is invalid.";

        $this->assertEquals($expectedMessage, $rule->message());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplier = Supplier::factory()->createQuietly();

        $date = Carbon::createFromFormat('Y-m-d', $this->date);
        SupplierHour::factory()->createQuietly([
            'supplier_id' => $this->supplier,
            'day'         => strtolower($date->format('l')),
            'from'        => '09:00 am',
            'to'          => '10:00 am',
        ]);

        $params = ['supplier' => $this->supplier, 'date' => $this->date];

        $this->workingHours = (App::make(GetWorkingHoursByDays::class, $params))->execute();
    }
}
