<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\WheelResource;
use App\Models\Wheel;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class WheelResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $diameter        = $this->faker->text(10);
        $width           = $this->faker->text(10);
        $bore            = $this->faker->text(25);
        $rotation        = $this->faker->text(10);
        $maxRpm          = $this->faker->numberBetween();
        $material        = $this->faker->text(25);
        $keyway          = $this->faker->text(25);
        $centerDisc      = $this->faker->text(25);
        $numberHubs      = $this->faker->numberBetween();
        $hubLock         = $this->faker->text(25);
        $numberSetscrews = $this->faker->text(10);
        $numberBlades    = $this->faker->numberBetween();
        $wheelType       = $this->faker->text(25);
        $driveType       = $this->faker->text(50);

        $wheel = Mockery::mock(Wheel::class);
        $wheel->shouldReceive('getAttribute')->withArgs(['diameter'])->once()->andReturn($diameter);
        $wheel->shouldReceive('getAttribute')->withArgs(['width'])->once()->andReturn($width);
        $wheel->shouldReceive('getAttribute')->withArgs(['bore'])->once()->andReturn($bore);
        $wheel->shouldReceive('getAttribute')->withArgs(['rotation'])->once()->andReturn($rotation);
        $wheel->shouldReceive('getAttribute')->withArgs(['max_rpm'])->once()->andReturn($maxRpm);
        $wheel->shouldReceive('getAttribute')->withArgs(['material'])->once()->andReturn($material);
        $wheel->shouldReceive('getAttribute')->withArgs(['keyway'])->once()->andReturn($keyway);
        $wheel->shouldReceive('getAttribute')->withArgs(['center_disc'])->once()->andReturn($centerDisc);
        $wheel->shouldReceive('getAttribute')->withArgs(['number_hubs'])->once()->andReturn($numberHubs);
        $wheel->shouldReceive('getAttribute')->withArgs(['hub_lock'])->once()->andReturn($hubLock);
        $wheel->shouldReceive('getAttribute')->withArgs(['number_setscrews'])->once()->andReturn($numberSetscrews);
        $wheel->shouldReceive('getAttribute')->withArgs(['number_blades'])->once()->andReturn($numberBlades);
        $wheel->shouldReceive('getAttribute')->withArgs(['wheel_type'])->once()->andReturn($wheelType);
        $wheel->shouldReceive('getAttribute')->withArgs(['drive_type'])->once()->andReturn($driveType);

        $resource = new WheelResource($wheel);

        $response = $resource->resolve();

        $data = [
            'diameter'         => $diameter,
            'width'            => $width,
            'bore'             => $bore,
            'rotation'         => $rotation,
            'max_rpm'          => $maxRpm,
            'material'         => $material,
            'keyway'           => $keyway,
            'center_disc'      => $centerDisc,
            'number_hubs'      => $numberHubs,
            'hub_lock'         => $hubLock,
            'number_setscrews' => $numberSetscrews,
            'number_blades'    => $numberBlades,
            'wheel_type'       => $wheelType,
            'drive_type'       => $driveType,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WheelResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
