<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V3\Account\AppVersionResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\AppVersion;
use App\Models\User;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class AppVersionResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(AppVersionResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);

        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturnNull();
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturnNull();
        $appVersion->shouldReceive('needsConfirm')->with($clientVersion = '0.0.0', $user)->once()->andReturnFalse();
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion)->once()->andReturnFalse();

        $resource = new AppVersionResource($appVersion, $clientVersion, $user);
        $response = $resource->resolve();

        $data = [
            'min'              => $min,
            'current'          => $current,
            'video_title'      => null,
            'video_url'        => null,
            'message'          => $message,
            'requires_update'  => false,
            'requires_confirm' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AppVersionResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user = Mockery::mock(User::class);

        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturn($videoTitle = 'video title');
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn($videoUrl = 'video url');
        $appVersion->shouldReceive('needsConfirm')->with($clientVersion = '0.0.0', $user)->once()->andReturnTrue();
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion)->once()->andReturnTrue();

        $resource = new AppVersionResource($appVersion, $clientVersion, $user);
        $response = $resource->resolve();

        $data = [
            'min'              => $min,
            'current'          => $current,
            'video_title'      => $videoTitle,
            'video_url'        => $videoUrl,
            'message'          => $message,
            'requires_update'  => true,
            'requires_confirm' => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AppVersionResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

}
