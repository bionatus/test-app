<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\ContactorResource;
use App\Models\Contactor;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ContactorResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $poles               = $this->faker->text(25);
        $shunts              = $this->faker->text(25);
        $coilVoltage         = $this->faker->numberBetween();
        $operatingVoltage    = $this->faker->text(25);
        $ph                  = $this->faker->text(10);
        $hz                  = $this->faker->text(10);
        $fla                 = $this->faker->text(50);
        $lra                 = $this->faker->text(50);
        $connectionType      = $this->faker->text(25);
        $terminationType     = $this->faker->text(25);
        $resistiveAmps       = $this->faker->text(25);
        $noninductiveAmps    = $this->faker->numberBetween();
        $auxialliaryContacts = $this->faker->text(25);
        $pushToTestWindow    = $this->faker->text(25);
        $contactorType       = $this->faker->text(25);
        $height              = $this->faker->text(25);
        $width               = $this->faker->text(25);
        $length              = $this->faker->text(25);
        $coilType            = $this->faker->text(25);
        $maxHp               = $this->faker->text(200);
        $fuseClipSize        = $this->faker->numberBetween();
        $enclosureType       = $this->faker->text(50);
        $temperatureRating   = $this->faker->text(5);
        $currentSettingRange = $this->faker->text(50);
        $resetType           = $this->faker->text(25);
        $accessories         = $this->faker->text(100);
        $overloadRelays      = $this->faker->text(100);
        $overloadTime        = $this->faker->text(25);

        $contactor = Mockery::mock(Contactor::class);
        $contactor->shouldReceive('getAttribute')->withArgs(['poles'])->once()->andReturn($poles);
        $contactor->shouldReceive('getAttribute')->withArgs(['shunts'])->once()->andReturn($shunts);
        $contactor->shouldReceive('getAttribute')->withArgs(['coil_voltage'])->once()->andReturn($coilVoltage);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['operating_voltage'])
            ->once()
            ->andReturn($operatingVoltage);
        $contactor->shouldReceive('getAttribute')->withArgs(['ph'])->once()->andReturn($ph);
        $contactor->shouldReceive('getAttribute')->withArgs(['hz'])->once()->andReturn($hz);
        $contactor->shouldReceive('getAttribute')->withArgs(['fla'])->once()->andReturn($fla);
        $contactor->shouldReceive('getAttribute')->withArgs(['lra'])->once()->andReturn($lra);
        $contactor->shouldReceive('getAttribute')->withArgs(['connection_type'])->once()->andReturn($connectionType);
        $contactor->shouldReceive('getAttribute')->withArgs(['termination_type'])->once()->andReturn($terminationType);
        $contactor->shouldReceive('getAttribute')->withArgs(['resistive_amps'])->once()->andReturn($resistiveAmps);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['noninductive_amps'])
            ->once()
            ->andReturn($noninductiveAmps);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['auxialliary_contacts'])
            ->once()
            ->andReturn($auxialliaryContacts);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['push_to_test_window'])
            ->once()
            ->andReturn($pushToTestWindow);
        $contactor->shouldReceive('getAttribute')->withArgs(['contactor_type'])->once()->andReturn($contactorType);
        $contactor->shouldReceive('getAttribute')->withArgs(['height'])->once()->andReturn($height);
        $contactor->shouldReceive('getAttribute')->withArgs(['width'])->once()->andReturn($width);
        $contactor->shouldReceive('getAttribute')->withArgs(['length'])->once()->andReturn($length);
        $contactor->shouldReceive('getAttribute')->withArgs(['coil_type'])->once()->andReturn($coilType);
        $contactor->shouldReceive('getAttribute')->withArgs(['max_hp'])->once()->andReturn($maxHp);
        $contactor->shouldReceive('getAttribute')->withArgs(['fuse_clip_size'])->once()->andReturn($fuseClipSize);
        $contactor->shouldReceive('getAttribute')->withArgs(['enclosure_type'])->once()->andReturn($enclosureType);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['temperature_rating'])
            ->once()
            ->andReturn($temperatureRating);
        $contactor->shouldReceive('getAttribute')
            ->withArgs(['current_setting_range'])
            ->once()
            ->andReturn($currentSettingRange);
        $contactor->shouldReceive('getAttribute')->withArgs(['reset_type'])->once()->andReturn($resetType);
        $contactor->shouldReceive('getAttribute')->withArgs(['accessories'])->once()->andReturn($accessories);
        $contactor->shouldReceive('getAttribute')->withArgs(['overload_relays'])->once()->andReturn($overloadRelays);
        $contactor->shouldReceive('getAttribute')->withArgs(['overload_time'])->once()->andReturn($overloadTime);

        $resource = new ContactorResource($contactor);

        $response = $resource->resolve();

        $data = [
            'poles'                 => $poles,
            'shunts'                => $shunts,
            'coil_voltage'          => $coilVoltage,
            'operating_voltage'     => $operatingVoltage,
            'ph'                    => $ph,
            'hz'                    => $hz,
            'fla'                   => $fla,
            'lra'                   => $lra,
            'connection_type'       => $connectionType,
            'termination_type'      => $terminationType,
            'resistive_amps'        => $resistiveAmps,
            'noninductive_amps'     => $noninductiveAmps,
            'auxialliary_contacts'  => $auxialliaryContacts,
            'push_to_test_window'   => $pushToTestWindow,
            'contactor_type'        => $contactorType,
            'height'                => $height,
            'width'                 => $width,
            'length'                => $length,
            'coil_type'             => $coilType,
            'max_hp'                => $maxHp,
            'fuse_clip_size'        => $fuseClipSize,
            'enclosure_type'        => $enclosureType,
            'temperature_rating'    => $temperatureRating,
            'current_setting_range' => $currentSettingRange,
            'reset_type'            => $resetType,
            'accessories'           => $accessories,
            'overload_relays'       => $overloadRelays,
            'overload_time'         => $overloadTime,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ContactorResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
