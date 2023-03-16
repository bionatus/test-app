<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Setting;

use App\Http\Resources\LiveApi\V1\Setting\BaseResource;
use App\Models\Setting;
use App\Models\SettingSupplier;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_suppliers_value()
    {
        $settingSupplier = Mockery::mock(SettingSupplier::class);
        $settingSupplier->shouldReceive('getAttribute')->with('value')->once()->andReturnFalse();

        $settingSuppliers = Mockery::mock(Collection::class);
        $settingSuppliers->shouldReceive('first')->withNoArgs()->once()->andReturn($settingSupplier);
        $settingSuppliers->shouldReceive('isNotEmpty')->withNoArgs()->once()->andReturnTrue();

        $setting = Mockery::mock(Setting::class);
        $setting->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($slug = 'setting slug');
        $setting->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'setting name');
        $setting->shouldReceive('getAttribute')->with('value')->once()->andReturnTrue();
        $setting->shouldReceive('getAttribute')->with('settingSuppliers')->once()->andReturn($settingSuppliers);

        $resource = new BaseResource($setting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'name'  => $name,
            'value' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_default_value()
    {
        $settingSuppliers = Mockery::mock(Collection::class);
        $settingSuppliers->shouldReceive('isNotEmpty')->withNoArgs()->once()->andReturnFalse();

        $setting = Mockery::mock(Setting::class);
        $setting->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($slug = 'setting slug');
        $setting->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'setting name');
        $setting->shouldReceive('getAttribute')->with('settingSuppliers')->once()->andReturn($settingSuppliers);
        $setting->shouldReceive('getAttribute')->with('value')->once()->andReturnTrue();

        $resource = new BaseResource($setting);
        $response = $resource->resolve();

        $data = [
            'id'    => $slug,
            'name'  => $name,
            'value' => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
