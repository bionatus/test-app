<?php

namespace Tests\Unit\Http\Requests\Api\V3\Supply;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Supply\IndexRequest;
use App\Models\SupplyCategory;
use App\Rules\Supply\ValidSupplyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_requires_a_supply_category()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_CATEGORY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_CATEGORY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_supply_category_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_CATEGORY => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_CATEGORY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_CATEGORY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_supply_category_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_CATEGORY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_CATEGORY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_CATEGORY);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_supply_category_should_exist_in_database()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::SUPPLY_CATEGORY => 'non-existent-supply-category']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_CATEGORY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_CATEGORY);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_supply_category_should_not_have_children()
    {
        $category = SupplyCategory::factory()->create();
        SupplyCategory::factory()->usingParent($category)->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_CATEGORY => $category->getRouteKey()]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_CATEGORY]);
        $request->assertValidationMessages([(new ValidSupplyCategory())->message()]);
    }

    /** @test */
    public function its_search_string_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_search_string_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $supplyCategory = 'common-tools';
        SupplyCategory::factory()->name($supplyCategory)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SUPPLY_CATEGORY => $supplyCategory,
            RequestKeys::SEARCH_STRING   => 'example',
        ]);

        $request->assertValidationPassed();
    }
}
