<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\AirFilterResource;
use App\Models\AirFilter;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class AirFilterResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $mediaType        = $this->faker->text(50);
        $mervRating       = $this->faker->numberBetween();
        $nominalWidth     = $this->faker->text(10);
        $nominalLength    = $this->faker->text(10);
        $nominalDepth     = $this->faker->text(10);
        $actualWidth      = $this->faker->text(10);
        $actualLength     = $this->faker->text(10);
        $actualDepth      = $this->faker->text(10);
        $efficiency       = $this->faker->text(10);
        $maxOperatingTemp = $this->faker->text(10);

        $airFilter = Mockery::mock(AirFilter::class);
        $airFilter->shouldReceive('getAttribute')->withArgs(['media_type'])->once()->andReturn($mediaType);
        $airFilter->shouldReceive('getAttribute')->withArgs(['merv_rating'])->once()->andReturn($mervRating);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_width'])->once()->andReturn($nominalWidth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_length'])->once()->andReturn($nominalLength);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_depth'])->once()->andReturn($nominalDepth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_width'])->once()->andReturn($actualWidth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_length'])->once()->andReturn($actualLength);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_depth'])->once()->andReturn($actualDepth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['efficiency'])->once()->andReturn($efficiency);
        $airFilter->shouldReceive('getAttribute')
            ->withArgs(['max_operating_temp'])
            ->once()
            ->andReturn($maxOperatingTemp);

        $resource = new AirFilterResource($airFilter);

        $response = $resource->resolve();

        $data = [
            'media_type'         => $mediaType,
            'merv_rating'        => $mervRating,
            'nominal_width'      => $nominalWidth,
            'nominal_length'     => $nominalLength,
            'nominal_depth'      => $nominalDepth,
            'actual_width'       => $actualWidth,
            'actual_length'      => $actualLength,
            'actual_depth'       => $actualDepth,
            'efficiency'         => $efficiency,
            'max_operating_temp' => $maxOperatingTemp,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AirFilterResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
