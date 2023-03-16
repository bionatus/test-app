<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\ByNumber;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByNumberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_number()
    {
        $number = '555222810';
        Phone::factory()->count(2)->create();
        Phone::factory()->sequence(function(Sequence $sequence) use ($number) {
            return [
                'country_code' => $sequence->index + 1,
                'number'       => $number,
            ];
        })->count(3)->create();

        $phones = Phone::scoped(new ByNumber($number))->get();

        $this->assertCount(3, $phones);
    }
}
