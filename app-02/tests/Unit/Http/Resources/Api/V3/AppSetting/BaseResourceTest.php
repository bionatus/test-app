<?php

namespace Tests\Unit\Http\Resources\Api\V3\AppSetting;

use App\Http\Resources\Api\V3\AppSetting\BaseResource;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $appSetting = Mockery::mock(AppSetting::class);
        $appSetting->shouldReceive('getAttribute')->with('slug')->once()->andReturn($slug = 'slug-key');
        $appSetting->shouldReceive('getAttribute')->with('label')->once()->andReturn($label = 'url');
        $appSetting->shouldReceive('getAttribute')->with('value')->once()->andReturn($value = 'http://url.com');
        $appSetting->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'string');

        $resource = new BaseResource($appSetting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'label' => $label,
            'value' => $value,
            'type'  => $type,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $appSetting = Mockery::mock(AppSetting::class);
        $appSetting->shouldReceive('getAttribute')->with('slug')->once()->andReturn($slug = 'slug-key');
        $appSetting->shouldReceive('getAttribute')->with('label')->once()->andReturn($label = 'url');
        $appSetting->shouldReceive('getAttribute')->with('value')->once()->andReturnNull();
        $appSetting->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'string');

        $resource = new BaseResource($appSetting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'label' => $label,
            'value' => null,
            'type'  => $type,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
