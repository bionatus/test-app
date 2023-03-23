<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Models\OrderDelivery;
use App\Rules\CurriDelivery\ValidZipCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\TestCase;

class ValidZipCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider zipCodesProvider
     */
    public function it_denies_when_it_receives_an_invalid_zip_code(?string $invalidZipCode)
    {
        $rule = new ValidZipCode($invalidZipCode);

        $this->assertSame(Lang::get('validation.curri_zip_code'), $rule->message());
    }

    public function zipCodesProvider(): array
    {
        return [['94203'], [null]];
    }

    /** @test */
    public function it_passes_with_a_valid_zip_code()
    {
        $rule = new ValidZipCode('85001');

        $this->assertTrue($rule->passes('attribute', OrderDelivery::TYPE_CURRI_DELIVERY));
    }
}
