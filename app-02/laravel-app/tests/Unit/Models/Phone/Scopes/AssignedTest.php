<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\Assigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_verified()
    {
        Phone::factory()->count(2)->create();
        Phone::factory()->withUser()->count(3)->create();

        $phones = Phone::scoped(new Assigned())->get();

        $this->assertCount(3, $phones);
    }
}
