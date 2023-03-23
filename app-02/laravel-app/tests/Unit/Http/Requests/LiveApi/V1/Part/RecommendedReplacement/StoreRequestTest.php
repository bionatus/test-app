<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Part\RecommendedReplacement;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Part\RecommendedReplacement\StoreRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_brand()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::BRAND])]);
    }

    /** @test */
    public function its_brand_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BRAND => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::BRAND])]);
    }

    /** @test */
    public function its_brand_must_be_2_characters_minimum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BRAND => 'a']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => RequestKeys::BRAND, 'min' => 2]),
        ]);
    }

    /** @test */
    public function its_brand_must_be_255_characters_maximum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BRAND => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::BRAND, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_part_number()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PART_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PART_NUMBER);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_part_number_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PART_NUMBER => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PART_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PART_NUMBER);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_part_number_must_be_2_characters_minimum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PART_NUMBER => 'a']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PART_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PART_NUMBER);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 2]),
        ]);
    }

    /** @test */
    public function its_part_number_must_be_255_characters_maximum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PART_NUMBER => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PART_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PART_NUMBER);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_note_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NOTE])]);
    }

    /** @test */
    public function its_note_must_be_255_characters_maximum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::NOTE, 'max' => 255]),
        ]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_data(?string $note)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::BRAND       => 'a brand',
            RequestKeys::PART_NUMBER => 'a part number',
            RequestKeys::NOTE        => $note,
        ]);

        $request->assertValidationPassed();
    }

    public function dataProvider(): array
    {
        return [
            [null],
            ['a note'],
        ];
    }
}
