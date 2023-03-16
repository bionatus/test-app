<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifiedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_verified()
    {
        Phone::factory()->unverified()->count(2)->create();
        Phone::factory()->verified()->count(3)->create();

        $phones = Phone::scoped(new Verified())->get();

        $this->assertCount(3, $phones);
    }
}
