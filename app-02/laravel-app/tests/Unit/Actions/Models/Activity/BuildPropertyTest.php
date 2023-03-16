<?php

namespace Tests\Unit\Actions\Models\Activity;

use App\Actions\Models\Activity\BuildProperty;
use App\Actions\Models\Activity\Contracts\Executable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class BuildPropertyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(BuildProperty::class);

        $this->assertTrue($reflection->implementsInterface(Executable::class));
    }

    /** @test */
    public function it_builds_a_property_base_on_key_value()
    {
        $property = [
            'key' => 'value',
        ];

        $action   = new BuildProperty('key', 'value');
        $resource = $action->execute();

        $this->assertEquals($property, $resource);
    }
}
