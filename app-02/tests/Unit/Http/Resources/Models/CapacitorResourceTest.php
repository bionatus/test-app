<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\CapacitorResource;
use App\Models\Capacitor;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CapacitorResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $microfarads           = $this->faker->text(50);
        $voltage               = $this->faker->text(25);
        $shape                 = $this->faker->text(25);
        $tolerance             = $this->faker->text(10);
        $operating_temperature = $this->faker->text(50);
        $depth                 = $this->faker->text(10);
        $height                = $this->faker->text(10);
        $width                 = $this->faker->text(25);
        $partNumberCorrection  = $this->faker->text(100);
        $notes                 = $this->faker->text();

        $capacitor = Mockery::mock(Capacitor::class);
        $capacitor->shouldReceive('getAttribute')->withArgs(['microfarads'])->once()->andReturn($microfarads);
        $capacitor->shouldReceive('getAttribute')->withArgs(['voltage'])->once()->andReturn($voltage);
        $capacitor->shouldReceive('getAttribute')->withArgs(['shape'])->once()->andReturn($shape);
        $capacitor->shouldReceive('getAttribute')->withArgs(['tolerance'])->once()->andReturn($tolerance);
        $capacitor->shouldReceive('getAttribute')
            ->withArgs(['operating_temperature'])
            ->once()
            ->andReturn($operating_temperature);
        $capacitor->shouldReceive('getAttribute')->withArgs(['depth'])->once()->andReturn($depth);
        $capacitor->shouldReceive('getAttribute')->withArgs(['height'])->once()->andReturn($height);
        $capacitor->shouldReceive('getAttribute')->withArgs(['width'])->once()->andReturn($width);
        $capacitor->shouldReceive('getAttribute')
            ->withArgs(['part_number_correction'])
            ->once()
            ->andReturn($partNumberCorrection);
        $capacitor->shouldReceive('getAttribute')->withArgs(['notes'])->once()->andReturn($notes);

        $resource = new CapacitorResource($capacitor);

        $response = $resource->resolve();

        $data = [
            'microfarads'            => $microfarads,
            'voltage'                => $voltage,
            'shape'                  => $shape,
            'tolerance'              => $tolerance,
            'operating_temperature'  => $operating_temperature,
            'depth'                  => $depth,
            'height'                 => $height,
            'width'                  => $width,
            'part_number_correction' => $partNumberCorrection,
            'notes'                  => $notes,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CapacitorResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
