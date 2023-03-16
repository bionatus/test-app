<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App;
use App\Actions\Models\Supplier\GetWorkingHoursByDays;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Rules\OrderDelivery\Is30MinutesBefore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class Is30MinutesBeforeTest extends TestCase
{
    use RefreshDatabase;

    private array    $workingHours;
    private string   $date = '2023-02-10';
    private Supplier $supplier;

    /** @test */
    public function it_returns_false_if_date_time_provided_is_invalid()
    {
        Carbon::setTestNow($this->date . ' 17:31:00');

        $rule = new Is30MinutesBefore($this->workingHours);

        $this->assertFalse($rule->passes('attribute', 'not valid value'));
    }

    /** @test */
    public function it_returns_false_if_the_date_is_not_a_working_day_from_the_supplier()
    {
        Carbon::setTestNow($this->date . ' 17:31:00');

        $rule = new Is30MinutesBefore($this->workingHours);

        $this->assertFalse($rule->passes('attribute', $this->date));
    }

    /** @test */
    public function it_returns_true_if_the_date_is_a_working_day_and_time_is_valid_from_supplier()
    {
        Carbon::setTestNow($this->date . " 09:29:00");

        $rule = new Is30MinutesBefore($this->workingHours);

        $this->assertTrue($rule->passes('attribute', '10:00'));
    }

    /** @test */
    public function it_validates_if_the_date_is_a_working_day_and_time_is_past_30_minutes_from_now()
    {
        Carbon::setTestNow($this->date . " 09:31:00");

        $rule = new Is30MinutesBefore($this->workingHours);

        $this->assertFalse($rule->passes('attribute', '10:00'));
    }

    /** @test */
    public function it_has_custom_message()
    {
        Carbon::setTestNow($this->date . ' 17:29:00');

        $rule            = new Is30MinutesBefore($this->workingHours);
        $expectedMessage = "The end of the time range is less than 30 minutes from now.";

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

        $params = ['supplier' => $this->supplier, 'date' => $date->format('Y-m-d H:i')];

        $this->workingHours = (App::make(GetWorkingHoursByDays::class, $params))->execute();
    }
}
