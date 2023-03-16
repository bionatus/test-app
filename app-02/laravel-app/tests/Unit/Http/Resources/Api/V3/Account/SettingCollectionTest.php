<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V2\User\Setting\BaseResource;
use App\Http\Resources\Api\V3\Account\SettingCollection;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $user = User::factory()->create();
        SettingUser::factory()->usingUser($user)->create();
        $settingCollection = $user->allSettingUsers();

        $resource = new SettingCollection($user->allSettingUsers());
        $response = $resource->resolve();

        $data = [
            'data' => BaseResource::collection($settingCollection),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SettingCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
