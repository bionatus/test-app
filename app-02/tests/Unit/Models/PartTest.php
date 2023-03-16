<?php

namespace Tests\Unit\Models;

use App;
use App\Models\AirFilter;
use App\Models\IsOrderable;
use App\Models\Other;
use App\Models\Part;
use Illuminate\Support\Collection;
use ReflectionClass;

class PartTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Part::tableName(), [
            'id',
            'tip_id',
            'number',
            'type',
            'subtype',
            'brand',
            'published_at',
            'image',
            'ingress_protection',
            'certifications',
            'nema_rating',
            'subcategory',
        ]);
    }

    /** @test */
    public function it_knows_if_it_has_a_valid_type()
    {
        Collection::make([
            'air_filter',
            'belt',
            'capacitor',
            'compressor',
            'contactor',
            'control_board',
            'crankcase_heater',
            'fan_blade',
            'filter_drier_and_core',
            'gas_valve',
            'hard_start_kit',
            'igniter',
            'metering_device',
            'motor',
            'pressure_control',
            'relay_switch_timer_sequencer',
            'sensor',
            'sheave_and_pulley',
            'temperature_control',
            'wheel',
            'other',
        ])->each(function(string $type) {
            $part = new Part(['type' => $type]);
            $this->assertTrue($part->hasValidType());
        });

        $this->assertFalse((new Part(['type' => 'invalid']))->hasValidType());
    }

    /** @test */
    public function it_knows_if_is_other_type()
    {
        $airFilterPart = AirFilter::factory()->create()->part;
        $otherPart     = Other::factory()->create()->part;

        $this->assertFalse($airFilterPart->isOther());
        $this->assertTrue($otherPart->isOther());
    }

    /** @test */
    public function it_hides_its_part_number()
    {
        $part = Part::factory()->create(['number' => 'TestNumber000']);

        $this->assertSame('Tes************', $part->hiddenNumber());
    }

    /** @test */
    public function it_implements_is_orderable_interface()
    {
        $reflection = new ReflectionClass(Part::class);

        $this->assertTrue($reflection->implementsInterface(IsOrderable::class));
    }

    /** @test */
    public function it_returns_a_readable_version_of_the_type_attribute()
    {
        $part       = App::make(Part::class);
        $part->type = 'a_type';

        $this->assertSame('A Type', $part->readable_type);
    }
}
