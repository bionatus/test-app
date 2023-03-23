<?php

namespace Tests\Unit\Http\Requests\Api\V2\Tag;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Tag\IndexRequest;
use App\Models\Brand;
use App\Models\Series;
use App\Models\Tag;
use Illuminate\Support\Str;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_may_not_get_a_param()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationPassed();
    }

    /** @test */
    public function type_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => ['2']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function type_must_be_a_valid_option()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => ['invalid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function type_must_exist_if_brand_is_sent()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::BRAND => ['valid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function type_must_be_series_if_brand_is_sent()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::BRAND => ['valid'], RequestKeys::TYPE => ['invalid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function brand_id_must_be_an_existing_brand()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::BRAND => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => $this->getDisplayableAttribute(RequestKeys::BRAND)]),
        ]);
    }

    /** @test */
    public function type_must_exist_if_series_is_sent()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::SERIES => ['valid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function type_must_be_model_type_if_series_is_sent(Bool $expected, String $value)
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::SERIES => ['valid'], RequestKeys::TYPE => $value]);

        $request->assertValidationFailed();
        if ($expected){
            $request->assertValidationErrorsMissing([RequestKeys::TYPE]);
            return;
        }
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    public function typeProvider(): array
    {
        return [
            [true, 'model_type'],
            [false, 'other_value']
        ];
    }

    /** @test */
    public function series_id_must_be_an_existing_series()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::SERIES => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SERIES]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => $this->getDisplayableAttribute(RequestKeys::SERIES)]),
        ]);
    }

    /** @test */
    public function brand_and_series_cannot_be_sent_together()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::BRAND => ['valid'], RequestKeys::SERIES => ['valid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRAND, RequestKeys::SERIES]);
        $brandAttribute  = $this->getDisplayableAttribute(RequestKeys::BRAND);
        $seriesAttribute = $this->getDisplayableAttribute(RequestKeys::SERIES);
        $request->assertValidationMessages([
            Lang::get(RequestKeys::BRAND . ' is not allowed.', ['attribute' => $brandAttribute]),
            Lang::get(RequestKeys::SERIES . ' is not allowed.', ['attribute' => $seriesAttribute]),
        ]);
    }

    /**
     * @test
     *
     * @param array $data
     *
     * @dataProvider validDataProvider
     */
    public function it_should_pass_validation_for_valid_data_not_including_brand_nor_series(array $data = [])
    {
        $this->formRequest($this->requestClass)->put($data)->assertValidationPassed();
    }

    public function validDataProvider(): array
    {
        return [
            [],
            [[RequestKeys::PER_PAGE => 10]],
            [[RequestKeys::TYPE => Tag::TYPE_SERIES]],
            [[RequestKeys::PER_PAGE => 10, RequestKeys::TYPE => Tag::TYPE_SERIES]],
        ];
    }

    /** @test */
    public function it_should_pass_validation_for_valid_data_including_a_brand()
    {
        $this->refreshDatabaseForSingleTest();
        $brand = Brand::factory()->create();

        $this->formRequest($this->requestClass)->put([
            RequestKeys::PER_PAGE => 10,
            RequestKeys::TYPE     => Tag::TYPE_SERIES,
            RequestKeys::BRAND    => $brand->getRouteKey(),
        ])->assertValidationPassed();
    }

    /** @test */
    public function it_should_pass_validation_for_valid_data_including_a_series()
    {
        $this->refreshDatabaseForSingleTest();
        $series = Series::factory()->create();

        $this->formRequest($this->requestClass)->put([
            RequestKeys::PER_PAGE => 10,
            RequestKeys::TYPE     => Tag::TYPE_MODEL_TYPE,
            RequestKeys::SERIES   => $series->getRouteKey(),
        ])->assertValidationPassed();
    }

    public function getDisplayableAttribute($attribute)
    {
        return str_replace('_', ' ', Str::snake($attribute));
    }
}
