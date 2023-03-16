<?php

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\AutomationApi\V1\Mobile\SignupProcessController;
use App\Http\Middleware\AuthenticateAutomationUser;
use Illuminate\Routing\Router;

Route::middleware([AuthenticateAutomationUser::class])->group(function(Router $route) {
    $route->prefix('/mobile')->group(function(Router $route) {
        $route->get('/signup-process/{' . RouteParameters::UNVERIFIED_PHONE . '}', SignupProcessController::class)
            ->name(RouteNames::AUTOMATION_API_V1_MOBILE_SIGNUP_PROCESS);
    });
});
