<?php

namespace Tests\Unit\Models\Device\Scopes;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_token()
    {
        $token = 'a valid token';
        Device::factory()->create(['token' => $token]);
        Device::factory()->count(5)->create();

        $devices = Device::scoped(new Device\Scopes\ByToken($token))->get();
        $this->assertCount(1, $devices);
    }
}
