<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\Oem\Scopes\Alphabetically;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlphabeticallyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_model_alphabetically()
    {
        $attribute = 'model';
        $oems      = Oem::factory()->count(5)->create()->sortBy($attribute);

        $orderedOems = Oem::scoped(new Alphabetically($attribute))->get();

        $orderedOems->each(function(Oem $oem) use ($oems) {
            $this->assertSame($oems->shift()->getKey(), $oem->getKey());
        });
    }

    /** @test */
    public function it_orders_by_model_notes_alphabetically()
    {
        $attribute = 'model_notes';
        $oems      = Oem::factory()->count(5)->create()->sortBy($attribute);

        $orderedOems = Oem::scoped(new Alphabetically($attribute))->get();

        $orderedOems->each(function(Oem $oem) use ($oems) {
            $this->assertSame($oems->shift()->getKey(), $oem->getKey());
        });
    }
}
