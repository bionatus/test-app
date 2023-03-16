<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\BulkFavoriteSeries;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\Account\BulkFavoriteSeriesController;
use App\Http\Requests\Api\V3\Account\BulkFavoriteSeries\InvokeRequest;
use App\Models\Series;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see BulkFavoriteSeriesController */
class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_series_parameter_must_be_present()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SERIES]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SERIES);
        $request->assertValidationMessages([Lang::get('validation.present', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_series_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SERIES => 'string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SERIES]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SERIES);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_series_parameter_must_exist()
    {
        $this->refreshDatabaseForSingleTest();
        $request = $this->formRequest($this->requestClass, [RequestKeys::SERIES => [['invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SERIES . '.0']);
        $request->assertValidationMessages(['Each item in series must exist.']);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $series = Series::factory()->count(2)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SERIES => $series->pluck(Series::routeKeyName())->toArray(),
        ]);

        $request->assertValidationPassed();
    }
}
