<?php

namespace Tests\Unit\Providers;

use App;
use App\Constants\RouteParameters;
use App\Http\Middleware\AcceptsJSON;
use App\Http\Middleware\LogSupplierApiUsage;
use App\Http\Middleware\LogUserApiUsage;
use App\Http\Middleware\ProvideLatamUser;
use App\Http\Middleware\ProvideLiveUser;
use App\Http\Middleware\SetRelationsMorphMap;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Phone;
use App\Models\Setting;
use App\Models\SupplyCategory;
use App\Models\SupportCallCategory;
use App\Providers\RouteServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;
use Route;
use Tests\TestCase;

/** @see RouteServiceProvider */
class RouteServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_invalid_unverified_phone()
    {
        $unverifiedPhone = '123456';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::UNVERIFIED_PHONE . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $unverifiedPhone);
    }

    /** @test */
    public function it_should_pass_a_model_on_existing_unverified_phone()
    {
        $phone = Phone::factory()->create();

        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::UNVERIFIED_PHONE . '}', fn(Phone $phone) => $phone);

        $response = $this->withoutExceptionHandling()->get('/test-route/' . $phone->fullNumber());

        $this->assertInstanceOf(Phone::class, $returnedPhone = $response->getOriginalContent());
        $this->assertSame($phone->getKey(), $returnedPhone->getKey());
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_invalid_assigned_verified_phone()
    {
        $unverifiedPhone = '123456';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::ASSIGNED_VERIFIED_PHONE . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $unverifiedPhone);
    }

    /** @test */
    public function it_should_pass_a_model_on_existing_assigned_verified_phone()
    {
        $phone = Phone::factory()->withUser()->verified()->create();

        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::ASSIGNED_VERIFIED_PHONE . '}', fn(Phone $phone) => $phone);

        $response = $this->withoutExceptionHandling()->get('/test-route/' . $phone->fullNumber());

        $this->assertInstanceOf(Phone::class, $returnedPhone = $response->getOriginalContent());
        $this->assertSame($phone->getKey(), $returnedPhone->getKey());
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_invalid_country()
    {
        $unverifiedPhone = '123456';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::COUNTRY . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $unverifiedPhone);
    }

    /** @test */
    public function it_should_pass_a_model_on_existing_country()
    {
        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::COUNTRY . '}', fn(Country $country) => $country);

        $response = $this->withoutExceptionHandling()->get('/test-route/US');

        /** @var $returned Country */
        $this->assertInstanceOf(Country::class, $returned = $response->getOriginalContent());
        $this->assertSame('US', $returned->getCode());
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_a_non_existing_supply_category()
    {
        $invalidSupplyCategory = 'invalid-supply-category';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::SUPPLY_CATEGORY . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $invalidSupplyCategory);
    }

    /** @test */
    public function it_should_pass_a_model_on_a_valid_supply_category()
    {
        $category = SupplyCategory::factory()->create();

        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::SUPPLY_CATEGORY . '}',
                fn(SupplyCategory $supplyCategory) => $supplyCategory);

        $response = $this->withoutExceptionHandling()->get('/test-route/' . $category->getRouteKey());

        $this->assertInstanceOf(SupplyCategory::class, $returnedCategory = $response->getOriginalContent());
        $this->assertSame($category->getKey(), $returnedCategory->getKey());
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_a_non_existing_support_call_category()
    {
        $invalidSupportCallCategory = 'invalid-support-call-category';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::SUPPORT_CALL_CATEGORY . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $invalidSupportCallCategory);
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_an_invalid_support_call_category()
    {
        $category    = SupportCallCategory::factory()->create();
        $subcategory = SupportCallCategory::factory()->usingParent($category)->create();

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::SUPPORT_CALL_CATEGORY . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $subcategory->getRouteKey());
    }

    /** @test */
    public function it_should_pass_a_model_on_a_valid_support_call_category()
    {
        $category = SupportCallCategory::factory()->create();

        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::SUPPORT_CALL_CATEGORY . '}',
                fn(SupportCallCategory $supportCallCategory) => $supportCallCategory);

        $response = $this->withoutExceptionHandling()->get('/test-route/' . $category->getRouteKey());

        $this->assertInstanceOf(SupportCallCategory::class, $returnedCategory = $response->getOriginalContent());
        $this->assertSame($category->getKey(), $returnedCategory->getKey());
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_a_non_existing_setting()
    {
        $invalidSetting = 'invalid-setting';

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::SETTING_USER . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $invalidSetting);
    }

    /** @test */
    public function it_should_generate_a_model_not_found_exception_on_a_supplier_applicable_setting()
    {

        $settingSupplier = Setting::factory()->applicableToSupplier()->create();

        Route::middleware('bindings')->get('test-route/{' . RouteParameters::SETTING_USER . '}');

        $this->expectException(ModelNotFoundException::class);

        $this->withoutExceptionHandling()->get('/test-route/' . $settingSupplier->getRouteKey());
    }

    /** @test */
    public function it_should_pass_a_model_on_a_valid_user_applicable_setting()
    {
        $settingUser = Setting::factory()->applicableToUser()->create();

        Route::middleware('bindings')
            ->get('test-route/{' . RouteParameters::SETTING_USER . '}', fn(Setting $setting) => $setting);

        $response = $this->withoutExceptionHandling()->get('/test-route/' . $settingUser->getRouteKey());

        $this->assertInstanceOf(Setting::class, $returnedSetting = $response->getOriginalContent());
        $this->assertSame($settingUser->getKey(), $returnedSetting->getKey());
    }

    /**
     * @test
     * @dataProvider middlewareProvider
     */
    public function it_applies_middlewares_to_routes($routesPrefix, $middlewares)
    {
        $router = App::make('router');

        $unprotected_routes = Collection::make($router->getRoutes())->filter(function($route) use (
            $routesPrefix,
            $middlewares
        ) {
            if (!preg_match($routesPrefix, $route->getName())) {
                return false;
            }

            return (array_diff($middlewares, $route->gatherMiddleware()));
        });

        $this->assertCount(0, $unprotected_routes);
    }

    public function middlewareProvider(): array
    {
        return [
            [
                'routesPrefix' => '/^api\.v2/',
                'middlewares'  => [
                    AcceptsJSON::class,
                    'api',
                    SetRelationsMorphMap::class,
                    ProvideLatamUser::class,
                    LogUserApiUsage::class,
                ],
            ],
            [
                'routesPrefix' => '/^api\.v3/',
                'middlewares'  => [
                    AcceptsJSON::class,
                    'api',
                    SetRelationsMorphMap::class,
                    ProvideLatamUser::class,
                    LogUserApiUsage::class,
                ],
            ],
            [
                'routesPrefix' => '/^api\.live\.v1/',
                'middlewares'  => [
                    AcceptsJSON::class,
                    'api',
                    ProvideLiveUser::class,
                    LogSupplierApiUsage::class,
                ],
            ],
            [
                'routesPrefix' => '/^webhook\.v1/',
                'middlewares'  => [
                    AcceptsJSON::class,
                    'bindings',
                    SetRelationsMorphMap::class,
                    ProvideLatamUser::class,
                ],
            ],
        ];
    }
}
