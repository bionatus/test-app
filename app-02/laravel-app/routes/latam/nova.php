<?php

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\Nova\Address\Country\StateController;
use App\Http\Controllers\Api\Nova\JobTitleController;
use Illuminate\Routing\Router;

Route::prefix('address/countries')->group(function(Router $route) {
    $route->get('{' . RouteParameters::COUNTRY . '}/states', [StateController::class, 'index'])
        ->name(RouteNames::API_NOVA_ADDRESS_COUNTRY_STATE_INDEX);
});

Route::get('job-titles', [JobTitleController::class, 'index'])->name(RouteNames::API_NOVA_JOB_TITLE_INDEX);
