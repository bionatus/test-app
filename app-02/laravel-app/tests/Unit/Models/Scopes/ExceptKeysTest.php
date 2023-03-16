<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Agent;
use App\Models\Scopes\ExceptKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptKeysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_excludes_keys()
    {
        $agents = Agent::factory()->count(10)->create();
        $except = $agents->splice(6);

        $filtered = Agent::scoped(new ExceptKeys($except->modelKeys()))->get();

        $this->assertEqualsCanonicalizing($agents->modelKeys(), $filtered->modelKeys());
    }
}
