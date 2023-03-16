<?php

namespace App\Http;

use App\Http\Middleware\ApiAuthentication;
use App\Http\Middleware\AssignGuard;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Spark\Http\Middleware\CreateFreshApiToken;
use Laravel\Spark\Http\Middleware\VerifyTeamIsSubscribed;
use Laravel\Spark\Http\Middleware\VerifyUserHasTeam;
use Laravel\Spark\Http\Middleware\VerifyUserIsDeveloper;
use Laravel\Spark\Http\Middleware\VerifyUserIsSubscribed;

class Kernel extends HttpKernel
{
    protected $middleware         = [
        CheckForMaintenanceMode::class,
        HandleCors::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        TrustProxies::class,
    ];
    protected $middlewareGroups   = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            CreateFreshApiToken::class,
        ],

        'api' => [
            'throttle:100,1',
            'bindings',
        ],
    ];
    protected $routeMiddleware    = [
        'api.auth'        => ApiAuthentication::class,
        'assign.guard'    => AssignGuard::class,
        'auth'            => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic'      => AuthenticateWithBasicAuth::class,
        'bindings'        => SubstituteBindings::class,
        'cache.headers'   => SetCacheHeaders::class,
        'can'             => Authorize::class,
        'dev'             => VerifyUserIsDeveloper::class,
        'guest'           => RedirectIfAuthenticated::class,
        'hasTeam'         => VerifyUserHasTeam::class,
        'signed'          => ValidateSignature::class,
        'subscribed'      => VerifyUserIsSubscribed::class,
        'teamSubscribed'  => VerifyTeamIsSubscribed::class,
        'throttle'        => ThrottleRequests::class,
        'verified'        => EnsureEmailIsVerified::class,
    ];
    protected $middlewarePriority = [
        StartSession::class,
        ShareErrorsFromSession::class,
        Authenticate::class,
        AuthenticateSession::class,
        SubstituteBindings::class,
        Authorize::class,
    ];
}
