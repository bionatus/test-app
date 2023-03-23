<?php

namespace Tests\Unit\Models;

use App\Models\Compressor;
use App\Models\IsPart;
use ReflectionException;

class CompressorTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Compressor::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Compressor::tableName(), [
            'id',
            'rated_refrigerant',
            'oil_type',
            'nominal_capacity_tons',
            'nominal_capacity_btuh',
            'voltage',
            'ph',
            'hz',
            'run_capacitor',
            'start_capacitor',
            'connection_type',
            'suction_inlet_diameter',
            'discharge_diameter',
            'number_of_cylinders',
            'number_of_unloaders',
            'crankcase_heater',
            'protection',
            'speed',
            'eer',
            'displacement',
            'nominal_hp',
            'nominal_power_watts',
            'fla',
            'lra',
            'rpm',
            'compressor_length',
            'compressor_width',
            'compressor_height',
            'run_type',
            'oil_factory_charge',
            'run_capacitor_part_number',
            'rated_conditions',
            'efficiency_type',
            'start_capacitor_part_number',
            'heating_type',
            'discharge_height',
            'suction_height',
            'capacitor_type',
            'unloader_type',
            'start_type',
            'process_connection_diameter',
            'oil_recharge',
            'capacity_watts',
            'capacity_mbh',
        ]);
    }
}
