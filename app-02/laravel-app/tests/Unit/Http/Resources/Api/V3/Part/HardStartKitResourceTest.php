<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\HardStartKitResource;
use App\Models\HardStartKit;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class HardStartKitResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $operatingVoltage = $this->faker->text(25);
        $maxHp            = $this->faker->text(10);
        $minHp            = $this->faker->text(10);
        $maxTons          = $this->faker->text(25);
        $minTons          = $this->faker->text(25);
        $maxCapacitance   = $this->faker->text(10);
        $minCapacitance   = $this->faker->text(10);
        $tolerance        = $this->faker->text(10);
        $torqueIncrease   = $this->faker->text(10);

        $hardStartKit = Mockery::mock(HardStartKit::class);
        $hardStartKit->shouldReceive('getAttribute')
            ->withArgs(['operating_voltage'])
            ->once()
            ->andReturn($operatingVoltage);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['max_hp'])->once()->andReturn($maxHp);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['min_hp'])->once()->andReturn($minHp);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['max_tons'])->once()->andReturn($maxTons);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['min_tons'])->once()->andReturn($minTons);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['max_capacitance'])->once()->andReturn($maxCapacitance);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['min_capacitance'])->once()->andReturn($minCapacitance);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['tolerance'])->once()->andReturn($tolerance);
        $hardStartKit->shouldReceive('getAttribute')->withArgs(['torque_increase'])->once()->andReturn($torqueIncrease);

        $resource = new HardStartKitResource($hardStartKit);

        $response = $resource->resolve();

        $data = [
            'operating_voltage' => $operatingVoltage,
            'max_hp'            => $maxHp,
            'min_hp'            => $minHp,
            'max_tons'          => $maxTons,
            'min_tons'          => $minTons,
            'max_capacitance'   => $maxCapacitance,
            'min_capacitance'   => $minCapacitance,
            'tolerance'         => $tolerance,
            'torque_increase'   => $torqueIncrease,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(HardStartKitResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
