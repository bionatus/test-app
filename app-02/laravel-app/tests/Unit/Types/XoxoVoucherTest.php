<?php

namespace Tests\Unit\Types;

use App\Types\XoxoVoucher;
use Exception;
use Illuminate\Support\Collection;
use Tests\TestCase;

class XoxoVoucherTest extends TestCase
{
    /**
     * @test
     * @dataProvider requiredKeyParametersProvider
     */
    public function it_throws_an_exception_if_required_values_are_empty(string $key)
    {
        $this->expectException(Exception::class);

        $item       = [
            'productId'          => 10,
            'name'               => 'fake name',
            'valueDenominations' => '10,20,50',
        ];
        $item[$key] = null;

        new XoxoVoucher($item);

        $this->expectExceptionMessage('Invalid voucher. The ' . $key . ' is required');
    }

    public function requiredParametersProvider(): array
    {
        return [
            ['productId'],
            ['name'],
            ['valueDenominations'],
        ];
    }

    /**
     * @test
     * @dataProvider requiredKeyParametersProvider
     */
    public function it_throws_an_exception_if_required_keys_are_not_defined(string $missingParameter)
    {
        $this->expectException(Exception::class);

        $item = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];
        unset($item[$missingParameter]);

        new XoxoVoucher($item);

        $this->expectExceptionMessage('Invalid voucher. The ' . $missingParameter . ' is required');
    }

    public function requiredKeyParametersProvider(): array
    {
        return [
            ['productId'],
            ['name'],
            ['imageUrl'],
            ['valueDenominations'],
            ['description'],
            ['redemptionInstructions'],
            ['termsAndConditionsInstructions'],
        ];
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_code()
    {
        $code = 10;
        $item = [
            'productId'                      => $code,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($code, $voucher->code());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_name()
    {
        $name = 'fake name';
        $item = [
            'productId'                      => 10,
            'name'                           => $name,
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($name, $voucher->name());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_image()
    {
        $imageUrl = 'fake image url';
        $item     = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => $imageUrl,
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($imageUrl, $voucher->image());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_value_denominations()
    {
        $valueDenominations = '10,20,50';

        $expected = Collection::make(['10', '20', '50']);
        $item     = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => $valueDenominations,
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($expected, $voucher->valueDenominations());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_description()
    {
        $description = 'fake description';
        $item        = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => $description,
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($description, $voucher->description());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_instructions()
    {
        $instructions = 'fake instructions';
        $item         = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => $instructions,
            'termsAndConditionsInstructions' => 'fake terms conditions',
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($instructions, $voucher->instructions());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_terms_and_conditions()
    {
        $termsAndConditions = 'fake terms conditions';
        $item               = [
            'productId'                      => 10,
            'name'                           => 'fake name',
            'imageUrl'                       => 'fake image url',
            'valueDenominations'             => '10,20,50',
            'description'                    => 'fake description',
            'redemptionInstructions'         => 'fake instructions',
            'termsAndConditionsInstructions' => $termsAndConditions,
        ];

        $voucher = new XoxoVoucher($item);

        $this->assertEquals($termsAndConditions, $voucher->termsConditions());
    }
}
