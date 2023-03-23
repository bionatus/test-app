<?php

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\BasecampApi\V1\SupportCallController;
use App\Http\Controllers\BasecampApi\V1\User\SupplierController as UserSupplierController;
use App\Http\Controllers\BasecampApi\V1\UserController;
use Illuminate\Routing\Router;
use App\Http\Middleware\AuthenticateBasecampUser;

Route::middleware([AuthenticateBasecampUser::class])->group(function(Router $route) {
    Route::prefix('users')->group(function(Router $route) {
        $route->get('/', [UserController::class, 'index'])->name(RouteNames::BASECAMP_API_V1_USER_INDEX);
        $route->prefix('/{' . RouteParameters::USER . '}')->group(function(Router $route) {
            $route->get('/', [UserController::class, 'show'])->name(RouteNames::BASECAMP_API_V1_USER_SHOW);
            $route->get('suppliers', UserSupplierController::class)
                ->name(RouteNames::BASECAMP_API_V1_USER_SUPPLIER_INDEX);
        });
    });

    Route::prefix('support-calls')->group(function() {
        Route::prefix('/{' . RouteParameters::SUPPORT_CALL . '}')->group(function() {
            Route::get('/', [SupportCallController::class, 'show'])
                ->name(RouteNames::BASECAMP_API_V1_SUPPORT_CALL_SHOW);
        });
    });
});
