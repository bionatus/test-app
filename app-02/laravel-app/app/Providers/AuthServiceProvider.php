<?php

namespace App\Providers;

use App\Constants\RoutePrefixes;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Request;
use Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::guessPolicyNamesUsing(function($modelClass) {
            return $this->guessPolicyName($modelClass);
        });
        Passport::routes();
    }

    private function guessPolicyName(string $modelClass): ?string
    {
        $uri     = Request::instance()->route()->uri();
        $api     = RoutePrefixes::API;
        $live    = RoutePrefixes::LIVE;
        $nova    = RoutePrefixes::NOVA;
        $novaApi = RoutePrefixes::NOVA_API;

        switch (true) {
            case Str::startsWith($uri, $api . '/'):
                $key = $api;
                break;
            case Str::startsWith($uri, $live . '/');
                $key = $live;
                break;
            case Str::startsWith($uri, $nova . '/');
            case Str::startsWith($uri, $novaApi . '/');
                $key = $nova;
                break;
            default:
                return $this->fallback($modelClass);
        }

        $prefix  = Str::studly($key);
        $folder  = $prefix ? "$prefix\\" : "";
        $rest    = Str::substr($uri, Str::length($key));
        $last    = Str::substr($rest, 2);
        $version = Str::before($last, '/');

        if (is_numeric($version)) {
            return $this->fallbackWithVersion($modelClass, $folder, $version);
        }

        return $this->fallback($modelClass, $folder);
    }

    private function fallback(string $class, ?string $folder = null): ?string
    {
        $baseName = class_basename($class);
        $class    = "App\\Policies\\$folder{$baseName}Policy";

        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    private function fallbackWithVersion(string $class, string $folder, int $version): ?string
    {
        $baseName = class_basename($class);

        do {
            $policy = "App\\Policies\\{$folder}V$version\\{$baseName}Policy";

            if (class_exists($policy)) {
                return $policy;
            }

            $version -= 1;
        } while ($version >= 1);

        return null;
    }
}
