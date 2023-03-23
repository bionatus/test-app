<?php

namespace Tests\Unit\Models\Substatus\Scopes;

use App\Models\Status;
use App\Models\Substatus;
use App\Models\Substatus\ByStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_substatus_by_status()
    {
        $status   = Status::factory()->create();
        $expected = Substatus::factory()->usingStatus($status)->create();
        Substatus::factory()->count(3)->create();
        
        $filtered = Substatus::scoped(new ByStatus($status->getKey()))->get();

        $this->assertSame($expected->getKey(), $filtered->first()->getKey());
    }
}
