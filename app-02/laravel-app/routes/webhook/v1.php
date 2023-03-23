<?php

use App\Constants\RouteNames;
use App\Http\Controllers\Webhook\Curri\TrackingController;
use Illuminate\Routing\Router;

Route::prefix('curri')->group(function(Router $route) {
    $route->post('/tracking', TrackingController::class)->name(RouteNames::WEBHOOK_V1_CURRI_TRACKING_STORE);
});

