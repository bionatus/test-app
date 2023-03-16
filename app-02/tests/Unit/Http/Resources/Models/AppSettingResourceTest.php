<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\AppSettingResource;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AppSettingResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $appSetting = Mockery::mock(AppSetting::class);
        $appSetting->shouldReceive('getAttribute')->with('slug')->once()->andReturn($slug = 'slug-key');
        $appSetting->shouldReceive('getAttribute')->with('label')->once()->andReturn($label = 'url');
        $appSetting->shouldReceive('getAttribute')->with('value')->once()->andReturn($value = 'http://url.com');
        $appSetting->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'string');

        $resource = new AppSettingResource($appSetting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'label' => $label,
            'value' => $value,
            'type'  => $type,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AppSettingResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
