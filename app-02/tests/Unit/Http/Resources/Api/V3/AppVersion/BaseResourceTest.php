<?php

namespace Tests\Unit\Http\Resources\Api\V3\AppVersion;

use App\Http\Resources\Api\V3\AppVersion\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\AppVersion;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturnNull();
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn($url = 'http://www.url.com');
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion = '0.0.0')->once()->andReturnFalse();

        $resource = new BaseResource($appVersion, $clientVersion);
        $response = $resource->resolve();

        $data = [
            'min'             => $min,
            'current'         => $current,
            'video_title'     => null,
            'video_url'       => $url,
            'message'         => $message,
            'requires_update' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturn($title = 'video title');
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn($url = 'http://www.url.com');
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion = '0.0.0')->once()->andReturnTrue();

        $resource = new BaseResource($appVersion, $clientVersion);
        $response = $resource->resolve();

        $data = [
            'min'             => $min,
            'current'         => $current,
            'video_title'     => $title,
            'video_url'       => $url,
            'message'         => $message,
            'requires_update' => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
