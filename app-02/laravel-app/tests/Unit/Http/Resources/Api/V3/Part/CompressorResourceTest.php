<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\CompressorResource;
use App\Models\Compressor;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CompressorResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $ratedRefrigerant     = $this->faker->text(50);
        $oilType              = $this->faker->text(25);
        $nominalCapacityTons  = $this->faker->text(10);
        $nominalCapacityBtuh  = $this->faker->text(50);
        $voltage              = $this->faker->text(25);
        $ph                   = $this->faker->text(25);
        $hz                   = $this->faker->text(25);
        $runCapacitor         = $this->faker->text(10);
        $startCapacitor       = $this->faker->text(50);
        $connectionType       = $this->faker->text(25);
        $suctionInletDiameter = $this->faker->text(25);
        $dischargeDiameter    = $this->faker->text(25);
        $numberOfCylinders    = $this->faker->numberBetween();
        $numberOfUnloaders    = $this->faker->numberBetween();
        $crankcaseHeater      = $this->faker->boolean();
        $protection           = $this->faker->text(50);
        $speed                = $this->faker->text(25);
        $eer                  = $this->faker->randomFloat(2, 0, 100);
        $displacement         = $this->faker->text(10);
        $nominalHp            = $this->faker->text(25);
        $nominalPowerWatts    = $this->faker->text(10);
        $fla                  = $this->faker->text(10);
        $lra                  = $this->faker->text(10);
        $rpm                  = $this->faker->text(10);
        $compressorLength     = $this->faker->text(10);
        $compressorWidth      = $this->faker->text(10);
        $compressorHeight     = $this->faker->text(10);

        $compressor = Mockery::mock(Compressor::class);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['rated_refrigerant'])
            ->once()
            ->andReturn($ratedRefrigerant);
        $compressor->shouldReceive('getAttribute')->withArgs(['oil_type'])->once()->andReturn($oilType);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['nominal_capacity_tons'])
            ->once()
            ->andReturn($nominalCapacityTons);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['nominal_capacity_btuh'])
            ->once()
            ->andReturn($nominalCapacityBtuh);
        $compressor->shouldReceive('getAttribute')->withArgs(['voltage'])->once()->andReturn($voltage);
        $compressor->shouldReceive('getAttribute')->withArgs(['ph'])->once()->andReturn($ph);
        $compressor->shouldReceive('getAttribute')->withArgs(['hz'])->once()->andReturn($hz);
        $compressor->shouldReceive('getAttribute')->withArgs(['run_capacitor'])->once()->andReturn($runCapacitor);
        $compressor->shouldReceive('getAttribute')->withArgs(['start_capacitor'])->once()->andReturn($startCapacitor);
        $compressor->shouldReceive('getAttribute')->withArgs(['connection_type'])->once()->andReturn($connectionType);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['suction_inlet_diameter'])
            ->once()
            ->andReturn($suctionInletDiameter);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['discharge_diameter'])
            ->once()
            ->andReturn($dischargeDiameter);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['number_of_cylinders'])
            ->once()
            ->andReturn($numberOfCylinders);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['number_of_unloaders'])
            ->once()
            ->andReturn($numberOfUnloaders);
        $compressor->shouldReceive('getAttribute')->withArgs(['crankcase_heater'])->once()->andReturn($crankcaseHeater);
        $compressor->shouldReceive('getAttribute')->withArgs(['protection'])->once()->andReturn($protection);
        $compressor->shouldReceive('getAttribute')->withArgs(['speed'])->once()->andReturn($speed);
        $compressor->shouldReceive('getAttribute')->withArgs(['eer'])->once()->andReturn($eer);
        $compressor->shouldReceive('getAttribute')->withArgs(['displacement'])->once()->andReturn($displacement);
        $compressor->shouldReceive('getAttribute')->withArgs(['nominal_hp'])->once()->andReturn($nominalHp);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['nominal_power_watts'])
            ->once()
            ->andReturn($nominalPowerWatts);
        $compressor->shouldReceive('getAttribute')->withArgs(['fla'])->once()->andReturn($fla);
        $compressor->shouldReceive('getAttribute')->withArgs(['lra'])->once()->andReturn($lra);
        $compressor->shouldReceive('getAttribute')->withArgs(['rpm'])->once()->andReturn($rpm);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['compressor_length'])
            ->once()
            ->andReturn($compressorLength);
        $compressor->shouldReceive('getAttribute')->withArgs(['compressor_width'])->once()->andReturn($compressorWidth);
        $compressor->shouldReceive('getAttribute')
            ->withArgs(['compressor_height'])
            ->once()
            ->andReturn($compressorHeight);

        $resource = new CompressorResource($compressor);

        $response = $resource->resolve();

        $data = [
            'rated_refrigerant'      => $ratedRefrigerant,
            'oil_type'               => $oilType,
            'nominal_capacity_tons'  => $nominalCapacityTons,
            'nominal_capacity_btuh'  => $nominalCapacityBtuh,
            'voltage'                => $voltage,
            'ph'                     => $ph,
            'hz'                     => $hz,
            'run_capacitor'          => $runCapacitor,
            'start_capacitor'        => $startCapacitor,
            'connection_type'        => $connectionType,
            'suction_inlet_diameter' => $suctionInletDiameter,
            'discharge_diameter'     => $dischargeDiameter,
            'number_of_cylinders'    => $numberOfCylinders,
            'number_of_unloaders'    => $numberOfUnloaders,
            'crankcase_heater'       => $crankcaseHeater,
            'protection'             => $protection,
            'speed'                  => $speed,
            'eer'                    => $eer,
            'displacement'           => $displacement,
            'nominal_hp'             => $nominalHp,
            'nominal_power_watts'    => $nominalPowerWatts,
            'fla'                    => $fla,
            'lra'                    => $lra,
            'rpm'                    => $rpm,
            'compressor_length'      => $compressorLength,
            'compressor_width'       => $compressorWidth,
            'compressor_height'      => $compressorHeight,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CompressorResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
