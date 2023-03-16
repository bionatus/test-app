<?php

namespace Tests\Unit\Actions\Models\Activity;

use App\Actions\Models\Activity\BuildResource;
use App\Actions\Models\Activity\Contracts\Executable;
use App\Http\Resources\Api\V2\Activity\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class BuildResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(BuildResource::class);

        $this->assertTrue($reflection->implementsInterface(Executable::class));
    }

    /** @test */
    public function it_builds_a_resource_base_on_post_resource()
    {
        $post = Post::factory()->create();

        $action   = new BuildResource($post, PostResource::class);
        $resource = $action->execute();

        $schema = $this->jsonSchema(PostResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($resource)));
    }
}
