<?php

namespace Tests\Unit\Observers;

use App;
use App\Actions\Models\Company\UpdateCoordinates;
use App\Models\Company;
use App\Observers\CompanyObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CompanyObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $model = Company::factory()->make(['uuid' => null]);

        $observer = new CompanyObserver();

        $observer->creating($model);

        $this->assertNotNull($model->uuid);
    }

    /** @test */
    public function it_reset_coordinates_when_invalid_zip_code_country_combination_is_detected()
    {
        $model = Mockery::mock(Company::class);
        $model->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnFalse();
        $model->shouldReceive('setAttribute')->withArgs(['latitude', null])->once();
        $model->shouldReceive('setAttribute')->withArgs(['longitude', null])->once();

        $observer = new CompanyObserver();

        $observer->saved($model);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_executes_action_when_conditions_are_met(
        bool $dirtyZipCode,
        bool $dirtyCountry
    ) {
        $action = Mockery::mock(UpdateCoordinates::class);
        $action->shouldReceive('execute')
            ->withNoArgs()
            ->times((int) ($dirtyZipCode || $dirtyCountry));
        App::bind(UpdateCoordinates::class, fn() => $action);

        $model = Mockery::mock(Company::class);
        $model->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnTrue();
        $model->shouldReceive('isDirty')->withArgs(['zip_code'])->once()->andReturn($dirtyZipCode);
        $model->shouldReceive('isDirty')->withArgs(['country'])->times((int) !$dirtyZipCode)->andReturn($dirtyCountry);

        $observer = new CompanyObserver();

        $observer->saved($model);
    }

    public function dataProvider(): array
    {
        return [
            [false, false],
            [false, true],
            [true, false],
            [true, true],
        ];
    }
}
