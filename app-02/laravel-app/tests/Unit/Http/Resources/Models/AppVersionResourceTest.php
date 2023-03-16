<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AppVersionResource;
use App\Models\AppVersion;
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
        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturnNull();
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn($url = 'http://www.url.com');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');

        $resource = new AppVersionResource($appVersion);
        $response = $resource->resolve();

        $data = [
            'min'         => $min,
            'current'     => $current,
            'video_title' => null,
            'video_url'   => $url,
            'message'     => $message,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AppVersionResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn($current = '2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn($min = '1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturn($title = 'video title');
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn($url = 'http://www.url.com');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = '<b>A bold text</b>');

        $resource = new AppVersionResource($appVersion);
        $response = $resource->resolve();

        $data = [
            'min'         => $min,
            'current'     => $current,
            'video_title' => $title,
            'video_url'   => $url,
            'message'     => $message,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(AppVersionResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
