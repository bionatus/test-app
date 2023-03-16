<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Replacement;
use App\Models\Scopes\OldestKey;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OldestKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_model_key()
    {
        $numberToCreate = 5;
        $replacements   = Replacement::factory()->count($numberToCreate)->sequence(function(Sequence $sequence) use (
            $numberToCreate
        ) {
            return [Replacement::keyName() => $numberToCreate - $sequence->index];
        })->create();

        $oldest = $replacements->reverse()->values();

        $this->assertEquals($oldest->pluck('id'), Replacement::scoped(new OldestKey())->pluck('id'));
    }
}
