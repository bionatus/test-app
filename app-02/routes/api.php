<?php

use App\Http\Middleware\AuthenticateUser;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the API routes for your application as
| the routes are automatically authenticated using the API guard and
| loaded automatically by this application's RouteServiceProvider.
|
*/

Route::middleware(['api.auth'])->group(function() {
    Route::get('/brands', 'BrandController@list');

    Route::get('/products/{product}', 'ProductController@info');
    Route::get('/products/{product}/conversion', 'ProductController@conversion');
    Route::get('/products/{product}/warnings', 'ProductWarningsController@index');

    Route::get('/layout/{version}', 'LayoutController@index');

    Route::get('/{brand}/products', 'BrandController@products');
    Route::get('/{brand}/products/search', 'BrandController@search');
});

Route::middleware([AuthenticateUser::class])->prefix('v2')->group(function() {
    // Legacy route
    Route::get('/brands', 'BrandController@list');
    // New route
    Route::get('/allbrands', 'BrandController@index');

    // Route::get('/products/{product}', 'ProductController@info');
    Route::get('/products/{product}/conversion', 'ProductController@conversion');
    Route::get('/products/{product}/warnings', 'ProductWarningsController@index');
    Route::get('/products/{product}/manuals', 'ProductController@manuals');

    Route::get('/layout/{version}', 'LayoutController@index');

    Route::get('/{brand}/products', 'BrandController@products');
    Route::get('/{brand}/products/search', 'BrandController@search');
});

Route::group(['namespace' => 'Api', 'prefix' => 'v2'], function() {
    Route::post('/login', 'AuthController@login');
    Route::post('/legacy/login', 'AuthController@legacyLogin');
    Route::get('/me', 'AuthController@getUser')->middleware(AuthenticateUser::class);
    Route::get('/logout', 'AuthController@logout')->middleware(AuthenticateUser::class);
    Route::get('/refresh', 'AuthController@refreshToken')->middleware(AuthenticateUser::class);
    Route::post('/reset-password', 'AuthController@sendResetLinkEmail');
    Route::get('/user', 'UsersController@index')->middleware(AuthenticateUser::class);
    Route::post('/user', 'UsersController@update')->middleware(AuthenticateUser::class);
    Route::get('/users/count', 'UsersController@count')->middleware(AuthenticateUser::class);
    Route::get('/users/count/accreditated', 'UsersController@countAccreditated')->middleware(AuthenticateUser::class);

    Route::get('/countries', 'CountriesController@index');

    Route::post('/create-account', 'AuthController@createAccount');
    Route::post('/complete-accreditation', 'AuthController@completeAccreditation')->middleware(AuthenticateUser::class);
    Route::post('/accept-terms', 'AuthController@acceptTerms')->middleware(AuthenticateUser::class);
    Route::post('/complete-registration', 'AuthController@completeRegistration');
    Route::post('/update-call-date', 'AuthController@handleCallRequest');
    // Route::post('/call-request', 'AuthController@handleCallRequest');

    Route::post('/notifications/create', 'NotificationController@create');
    Route::post('/notifications/remove', 'NotificationController@remove');
    Route::get('/notifications', 'NotificationController@get')->middleware(AuthenticateUser::class);
    Route::get('/notifications/status', 'NotificationController@status')->middleware(AuthenticateUser::class);
    Route::post('/notifications/read', 'NotificationController@read')->middleware(AuthenticateUser::class);

    Route::post('/statistics/update', 'StatisticsController@update')->middleware(AuthenticateUser::class);

    Route::get('/stores', 'StoreController@index');
    Route::get('/stores/search', 'SearchStoreController@index');
    Route::get('/stores/search/place', 'SearchStoreByPlaceController@index');
    Route::get('/address/search', 'AddressAutocompleteController@index');

    Route::get('/reviews', 'ReviewsController@index');
    Route::get('/reviews/media', 'ReviewsMediaController@index');
    Route::get('/reviews/{review}', 'ReviewsController@info');

    Route::get('/brands/{brandId}/series', 'BrandSeriesController@index');
    Route::get('/brands/{brandId}/series/{seriesId}/products', 'BrandSeriesProductsController@index');

    Route::get('/support/{code}/technician', 'SupportCodesController@index')->middleware(AuthenticateUser::class);
});
