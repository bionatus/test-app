<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\Except;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_excludes_provided_key()
    {
        Staff::factory()->count(10)->createQuietly();
        Staff::factory()->createQuietly(['email' => $email = 'test@email.com']);

        $filtered = Staff::scoped(new Except('email', $email))->get();

        $this->assertNotContains($email, $filtered->pluck('email'));
    }
}
