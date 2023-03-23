<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\SettingResource;
use App\Models\Setting;
use Mockery;
use Tests\TestCase;

class SettingResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $setting = Mockery::mock(Setting::class);
        $setting->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($slug = 'setting slug');
        $setting->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'setting name');
        $setting->shouldReceive('getAttribute')->with('value')->once()->andReturnTrue();

        $resource = new SettingResource($setting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'name'  => $name,
            'value' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SettingResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
