<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\ByFullNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByFullNumberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_full_number()
    {
        $countryCode = 1;
        $number      = 555222810;
        $fullNumber  = $countryCode . $number;
        Phone::factory()->create([
            'country_code' => $countryCode,
            'number'       => $number,
        ]);
        Phone::factory()->count(3)->create();

        $phone = Phone::query()->scoped(new ByFullNumber($fullNumber))->first();

        $this->assertInstanceOf(Phone::class, $phone);
        $this->assertSame($fullNumber, $phone->fullNumber());
    }

    /** @test */
    public function it_filters_out_by_full_number()
    {
        Phone::factory()->count(3)->create();

        $phone = Phone::query()->scoped(new ByFullNumber(1234567890))->first();

        $this->assertNull($phone);
    }
}
