<?php

namespace Tests\Unit\Http\Resources\Api\V2\User\Setting;

use App\Http\Resources\Api\V2\User\Setting\BaseResource;
use App\Models\Setting;
use App\Models\SettingUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $setting     = Setting::factory()->boolean()->create();
        $settingUser = SettingUser::factory()->usingSetting($setting)->create(['value' => 0]);

        $resource = new BaseResource($settingUser);
        $response = $resource->resolve();

        $data = [
            'id'    => $settingUser->setting->getRouteKey(),
            'name'  => $settingUser->setting->name,
            'type'  => 'boolean',
            'value' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
