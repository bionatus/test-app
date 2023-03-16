<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\MotorResource;
use App\Models\Motor;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class MotorResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $motorType              = $this->faker->text(25);
        $dutyRating             = $this->faker->text(50);
        $voltage                = $this->faker->text(100);
        $ph                     = $this->faker->numberBetween();
        $hz                     = $this->faker->numberBetween();
        $runCapacitorSize       = $this->faker->text(10);
        $rpm                    = $this->faker->text(100);
        $outputHp               = $this->faker->text(25);
        $frameType              = $this->faker->text(10);
        $rotation               = $this->faker->text(50);
        $speed                  = $this->faker->text(50);
        $shaftDiameter          = $this->faker->text(10);
        $shaftKeyway            = $this->faker->text(25);
        $bearingType            = $this->faker->text(50);
        $fla                    = $this->faker->text(25);
        $mountingType           = $this->faker->text(25);
        $shaftLength            = $this->faker->text(25);
        $replaceableBearings    = $this->faker->text(10);
        $motorDiameter          = $this->faker->text(10);
        $motorHeight            = $this->faker->text(10);
        $enclosureType          = $this->faker->text(25);
        $materialType           = $this->faker->text(25);
        $weight                 = $this->faker->text(10);
        $protection             = $this->faker->text(25);
        $rla                    = $this->faker->text(50);
        $lra                    = $this->faker->text(50);
        $serviceFactor          = $this->faker->text(50);
        $powerFactor            = $this->faker->text(50);
        $cfm                    = $this->faker->text(50);
        $startCapacitorSize     = $this->faker->text(50);
        $efficiency             = $this->faker->text(50);
        $alternatePartNumbers   = $this->faker->text(100);
        $outputWatts            = $this->faker->text(50);
        $inputWatts             = $this->faker->text(50);
        $ringSize               = $this->faker->text(50);
        $resilientRingDimension = $this->faker->text(50);
        $armatureAmps           = $this->faker->text(50);
        $fieldAmps              = $this->faker->text(50);
        $serviceFactorAmps      = $this->faker->text(50);
        $multiVoltage           = $this->faker->text(50);
        $rotationOrientation    = $this->faker->text(50);
        $mountingAngle          = $this->faker->text(50);
        $shaftType              = $this->faker->text(50);
        $conduitBoxOrientation  = $this->faker->text(50);
        $torqueType             = $this->faker->text(50);
        $driveType              = $this->faker->text(50);
        $misc                   = $this->faker->text(200);
        $armatureVoltage        = $this->faker->text(50);
        $fieldVoltage           = $this->faker->text(50);
        $startType              = $this->faker->text(50);
        $outputVoltage          = $this->faker->text(50);
        $trademarkName          = $this->faker->text(50);
        $modulePartNumber       = $this->faker->text(100);
        $includedWith           = $this->faker->text(100);
        $operatingTemperature   = $this->faker->text(50);
        $electricalNotes        = $this->faker->text(100);
        $constantTorqueSpeed    = $this->faker->text(100);
        $variableTorqueSpeed    = $this->faker->text(100);
        $startTorque            = $this->faker->text(100);
        $runTorque              = $this->faker->text(100);
        $fullLoadTorque         = $this->faker->text(100);
        $loadFactor             = $this->faker->text(100);
        $frameDiameter          = $this->faker->text(100);
        $coolingType            = $this->faker->text(100);
        $notes                  = $this->faker->text(100);
        $aDimension             = $this->faker->text(100);
        $bDimension             = $this->faker->text(100);
        $totalLength            = $this->faker->text(100);
        $efficiencyType         = $this->faker->text(50);
        $shaftDimensions        = $this->faker->text(50);
        $fanBladeDimensions     = $this->faker->text(50);
        $stack                  = $this->faker->text(50);
        $partType               = $this->faker->text(50);
        $leadLength             = $this->faker->text(50);
        $capacitorPartNumber    = $this->faker->text(50);
        $eisaRating             = $this->faker->text(50);
        $bisscRating            = $this->faker->text(50);
        $shaftOrientation       = $this->faker->text(50);
        $runType                = $this->faker->text(50);
        $numberOfPoles          = $this->faker->text(50);
        $correctPartNumber      = $this->faker->text(50);
        $mountPartNumber        = $this->faker->text(50);
        $iecRating              = $this->faker->text(50);
        $brakingTorque          = $this->faker->text(50);
        $windingType            = $this->faker->text(100);
        $sourceInfo             = $this->faker->text(200);
        $inputVoltage           = $this->faker->text(50);
        $mechanicalHp           = $this->faker->text(50);
        $hubToHub               = $this->faker->text(50);
        $nominalCapacity        = $this->faker->text(50);
        $wheelDimensions        = $this->faker->text(50);

        $motor = Mockery::mock(Motor::class);
        $motor->shouldReceive('getAttribute')->withArgs(['motor_type'])->once()->andReturn($motorType);
        $motor->shouldReceive('getAttribute')->withArgs(['duty_rating'])->once()->andReturn($dutyRating);
        $motor->shouldReceive('getAttribute')->withArgs(['voltage'])->once()->andReturn($voltage);
        $motor->shouldReceive('getAttribute')->withArgs(['ph'])->once()->andReturn($ph);
        $motor->shouldReceive('getAttribute')->withArgs(['hz'])->once()->andReturn($hz);
        $motor->shouldReceive('getAttribute')->withArgs(['run_capacitor_size'])->once()->andReturn($runCapacitorSize);
        $motor->shouldReceive('getAttribute')->withArgs(['rpm'])->once()->andReturn($rpm);
        $motor->shouldReceive('getAttribute')->withArgs(['output_hp'])->once()->andReturn($outputHp);
        $motor->shouldReceive('getAttribute')->withArgs(['frame_type'])->once()->andReturn($frameType);
        $motor->shouldReceive('getAttribute')->withArgs(['rotation'])->once()->andReturn($rotation);
        $motor->shouldReceive('getAttribute')->withArgs(['speed'])->once()->andReturn($speed);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_diameter'])->once()->andReturn($shaftDiameter);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_keyway'])->once()->andReturn($shaftKeyway);
        $motor->shouldReceive('getAttribute')->withArgs(['bearing_type'])->once()->andReturn($bearingType);
        $motor->shouldReceive('getAttribute')->withArgs(['fla'])->once()->andReturn($fla);
        $motor->shouldReceive('getAttribute')->withArgs(['mounting_type'])->once()->andReturn($mountingType);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_length'])->once()->andReturn($shaftLength);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['replaceable_bearings'])
            ->once()
            ->andReturn($replaceableBearings);
        $motor->shouldReceive('getAttribute')->withArgs(['motor_diameter'])->once()->andReturn($motorDiameter);
        $motor->shouldReceive('getAttribute')->withArgs(['motor_height'])->once()->andReturn($motorHeight);
        $motor->shouldReceive('getAttribute')->withArgs(['enclosure_type'])->once()->andReturn($enclosureType);
        $motor->shouldReceive('getAttribute')->withArgs(['material_type'])->once()->andReturn($materialType);
        $motor->shouldReceive('getAttribute')->withArgs(['weight'])->once()->andReturn($weight);
        $motor->shouldReceive('getAttribute')->withArgs(['protection'])->once()->andReturn($protection);
        $motor->shouldReceive('getAttribute')->withArgs(['rla'])->once()->andReturn($rla);
        $motor->shouldReceive('getAttribute')->withArgs(['lra'])->once()->andReturn($lra);
        $motor->shouldReceive('getAttribute')->withArgs(['service_factor'])->once()->andReturn($serviceFactor);
        $motor->shouldReceive('getAttribute')->withArgs(['power_factor'])->once()->andReturn($powerFactor);
        $motor->shouldReceive('getAttribute')->withArgs(['cfm'])->once()->andReturn($cfm);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['start_capacitor_size'])
            ->once()
            ->andReturn($startCapacitorSize);
        $motor->shouldReceive('getAttribute')->withArgs(['efficiency'])->once()->andReturn($efficiency);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['alternate_part_numbers'])
            ->once()
            ->andReturn($alternatePartNumbers);
        $motor->shouldReceive('getAttribute')->withArgs(['output_watts'])->once()->andReturn($outputWatts);
        $motor->shouldReceive('getAttribute')->withArgs(['input_watts'])->once()->andReturn($inputWatts);
        $motor->shouldReceive('getAttribute')->withArgs(['ring_size'])->once()->andReturn($ringSize);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['resilient_ring_dimension'])
            ->once()
            ->andReturn($resilientRingDimension);
        $motor->shouldReceive('getAttribute')->withArgs(['armature_amps'])->once()->andReturn($armatureAmps);
        $motor->shouldReceive('getAttribute')->withArgs(['field_amps'])->once()->andReturn($fieldAmps);
        $motor->shouldReceive('getAttribute')->withArgs(['service_factor_amps'])->once()->andReturn($serviceFactorAmps);
        $motor->shouldReceive('getAttribute')->withArgs(['multi_voltage'])->once()->andReturn($multiVoltage);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['rotation_orientation'])
            ->once()
            ->andReturn($rotationOrientation);
        $motor->shouldReceive('getAttribute')->withArgs(['mounting_angle'])->once()->andReturn($mountingAngle);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_type'])->once()->andReturn($shaftType);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['conduit_box_orientation'])
            ->once()
            ->andReturn($conduitBoxOrientation);
        $motor->shouldReceive('getAttribute')->withArgs(['torque_type'])->once()->andReturn($torqueType);
        $motor->shouldReceive('getAttribute')->withArgs(['drive_type'])->once()->andReturn($driveType);
        $motor->shouldReceive('getAttribute')->withArgs(['misc'])->once()->andReturn($misc);

        $motor->shouldReceive('getAttribute')->withArgs(['armature_voltage'])->once()->andReturn($armatureVoltage);
        $motor->shouldReceive('getAttribute')->withArgs(['field_voltage'])->once()->andReturn($fieldVoltage);
        $motor->shouldReceive('getAttribute')->withArgs(['start_type'])->once()->andReturn($startType);
        $motor->shouldReceive('getAttribute')->withArgs(['output_voltage'])->once()->andReturn($outputVoltage);
        $motor->shouldReceive('getAttribute')->withArgs(['trademark_name'])->once()->andReturn($trademarkName);
        $motor->shouldReceive('getAttribute')->withArgs(['module_part_number'])->once()->andReturn($modulePartNumber);
        $motor->shouldReceive('getAttribute')->withArgs(['included_with'])->once()->andReturn($includedWith);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['operating_temperature'])
            ->once()
            ->andReturn($operatingTemperature);
        $motor->shouldReceive('getAttribute')->withArgs(['electrical_notes'])->once()->andReturn($electricalNotes);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['constant_torque_speed'])
            ->once()
            ->andReturn($constantTorqueSpeed);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['variable_torque_speed'])
            ->once()
            ->andReturn($variableTorqueSpeed);
        $motor->shouldReceive('getAttribute')->withArgs(['start_torque'])->once()->andReturn($startTorque);
        $motor->shouldReceive('getAttribute')->withArgs(['run_torque'])->once()->andReturn($runTorque);
        $motor->shouldReceive('getAttribute')->withArgs(['full_load_torque'])->once()->andReturn($fullLoadTorque);
        $motor->shouldReceive('getAttribute')->withArgs(['load_factor'])->once()->andReturn($loadFactor);
        $motor->shouldReceive('getAttribute')->withArgs(['frame_diameter'])->once()->andReturn($frameDiameter);
        $motor->shouldReceive('getAttribute')->withArgs(['cooling_type'])->once()->andReturn($coolingType);
        $motor->shouldReceive('getAttribute')->withArgs(['notes'])->once()->andReturn($notes);
        $motor->shouldReceive('getAttribute')->withArgs(['a_dimension'])->once()->andReturn($aDimension);
        $motor->shouldReceive('getAttribute')->withArgs(['b_dimension'])->once()->andReturn($bDimension);
        $motor->shouldReceive('getAttribute')->withArgs(['total_length'])->once()->andReturn($totalLength);
        $motor->shouldReceive('getAttribute')->withArgs(['efficiency_type'])->once()->andReturn($efficiencyType);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_dimensions'])->once()->andReturn($shaftDimensions);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['fan_blade_dimensions'])
            ->once()
            ->andReturn($fanBladeDimensions);
        $motor->shouldReceive('getAttribute')->withArgs(['stack'])->once()->andReturn($stack);
        $motor->shouldReceive('getAttribute')->withArgs(['part_type'])->once()->andReturn($partType);
        $motor->shouldReceive('getAttribute')->withArgs(['lead_length'])->once()->andReturn($leadLength);
        $motor->shouldReceive('getAttribute')
            ->withArgs(['capacitor_part_number'])
            ->once()
            ->andReturn($capacitorPartNumber);
        $motor->shouldReceive('getAttribute')->withArgs(['eisa_rating'])->once()->andReturn($eisaRating);
        $motor->shouldReceive('getAttribute')->withArgs(['bissc_rating'])->once()->andReturn($bisscRating);
        $motor->shouldReceive('getAttribute')->withArgs(['shaft_orientation'])->once()->andReturn($shaftOrientation);
        $motor->shouldReceive('getAttribute')->withArgs(['run_type'])->once()->andReturn($runType);
        $motor->shouldReceive('getAttribute')->withArgs(['number_of_poles'])->once()->andReturn($numberOfPoles);
        $motor->shouldReceive('getAttribute')->withArgs(['correct_part_number'])->once()->andReturn($correctPartNumber);
        $motor->shouldReceive('getAttribute')->withArgs(['mount_part_number'])->once()->andReturn($mountPartNumber);
        $motor->shouldReceive('getAttribute')->withArgs(['iec_rating'])->once()->andReturn($iecRating);
        $motor->shouldReceive('getAttribute')->withArgs(['braking_torque'])->once()->andReturn($brakingTorque);
        $motor->shouldReceive('getAttribute')->withArgs(['winding_type'])->once()->andReturn($windingType);
        $motor->shouldReceive('getAttribute')->withArgs(['source_info'])->once()->andReturn($sourceInfo);
        $motor->shouldReceive('getAttribute')->withArgs(['input_voltage'])->once()->andReturn($inputVoltage);
        $motor->shouldReceive('getAttribute')->withArgs(['mechanical_hp'])->once()->andReturn($mechanicalHp);
        $motor->shouldReceive('getAttribute')->withArgs(['hub_to_hub'])->once()->andReturn($hubToHub);
        $motor->shouldReceive('getAttribute')->withArgs(['nominal_capacity'])->once()->andReturn($nominalCapacity);
        $motor->shouldReceive('getAttribute')->withArgs(['wheel_dimensions'])->once()->andReturn($wheelDimensions);

        $resource = new MotorResource($motor);

        $response = $resource->resolve();

        $data = [
            'motor_type'               => $motorType,
            'duty_rating'              => $dutyRating,
            'voltage'                  => $voltage,
            'ph'                       => $ph,
            'hz'                       => $hz,
            'run_capacitor_size'       => $runCapacitorSize,
            'rpm'                      => $rpm,
            'output_hp'                => $outputHp,
            'frame_type'               => $frameType,
            'rotation'                 => $rotation,
            'speed'                    => $speed,
            'shaft_diameter'           => $shaftDiameter,
            'shaft_keyway'             => $shaftKeyway,
            'bearing_type'             => $bearingType,
            'fla'                      => $fla,
            'mounting_type'            => $mountingType,
            'shaft_length'             => $shaftLength,
            'replaceable_bearings'     => $replaceableBearings,
            'motor_diameter'           => $motorDiameter,
            'motor_height'             => $motorHeight,
            'enclosure_type'           => $enclosureType,
            'material_type'            => $materialType,
            'weight'                   => $weight,
            'protection'               => $protection,
            'rla'                      => $rla,
            'lra'                      => $lra,
            'service_factor'           => $serviceFactor,
            'power_factor'             => $powerFactor,
            'cfm'                      => $cfm,
            'start_capacitor_size'     => $startCapacitorSize,
            'efficiency'               => $efficiency,
            'alternate_part_numbers'   => $alternatePartNumbers,
            'output_watts'             => $outputWatts,
            'input_watts'              => $inputWatts,
            'ring_size'                => $ringSize,
            'resilient_ring_dimension' => $resilientRingDimension,
            'armature_amps'            => $armatureAmps,
            'field_amps'               => $fieldAmps,
            'service_factor_amps'      => $serviceFactorAmps,
            'multi_voltage'            => $multiVoltage,
            'rotation_orientation'     => $rotationOrientation,
            'mounting_angle'           => $mountingAngle,
            'shaft_type'               => $shaftType,
            'conduit_box_orientation'  => $conduitBoxOrientation,
            'torque_type'              => $torqueType,
            'drive_type'               => $driveType,
            'misc'                     => $misc,
            'armature_voltage'         => $armatureVoltage,
            'field_voltage'            => $fieldVoltage,
            'start_type'               => $startType,
            'output_voltage'           => $outputVoltage,
            'trademark_name'           => $trademarkName,
            'module_part_number'       => $modulePartNumber,
            'included_with'            => $includedWith,
            'operating_temperature'    => $operatingTemperature,
            'electrical_notes'         => $electricalNotes,
            'constant_torque_speed'    => $constantTorqueSpeed,
            'variable_torque_speed'    => $variableTorqueSpeed,
            'start_torque'             => $startTorque,
            'run_torque'               => $runTorque,
            'full_load_torque'         => $fullLoadTorque,
            'load_factor'              => $loadFactor,
            'frame_diameter'           => $frameDiameter,
            'cooling_type'             => $coolingType,
            'notes'                    => $notes,
            'a_dimension'              => $aDimension,
            'b_dimension'              => $bDimension,
            'total_length'             => $totalLength,
            'efficiency_type'          => $efficiencyType,
            'shaft_dimensions'         => $shaftDimensions,
            'fan_blade_dimensions'     => $fanBladeDimensions,
            'stack'                    => $stack,
            'part_type'                => $partType,
            'lead_length'              => $leadLength,
            'capacitor_part_number'    => $capacitorPartNumber,
            'eisa_rating'              => $eisaRating,
            'bissc_rating'             => $bisscRating,
            'shaft_orientation'        => $shaftOrientation,
            'run_type'                 => $runType,
            'number_of_poles'          => $numberOfPoles,
            'correct_part_number'      => $correctPartNumber,
            'mount_part_number'        => $mountPartNumber,
            'iec_rating'               => $iecRating,
            'braking_torque'           => $brakingTorque,
            'winding_type'             => $windingType,
            'source_info'              => $sourceInfo,
            'input_voltage'            => $inputVoltage,
            'mechanical_hp'            => $mechanicalHp,
            'hub_to_hub'               => $hubToHub,
            'nominal_capacity'         => $nominalCapacity,
            'wheel_dimensions'         => $wheelDimensions,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(MotorResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
