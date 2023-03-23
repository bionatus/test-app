<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\Unverified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnverifiedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_unverified()
    {
        Phone::factory()->verified()->count(2)->create();
        Phone::factory()->unverified()->count(3)->create();

        $unverifiedPhones = Phone::scoped(new Unverified())->get();

        $this->assertCount(3, $unverifiedPhones);
    }
}
