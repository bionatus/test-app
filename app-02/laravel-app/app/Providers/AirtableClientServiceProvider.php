<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Lib\AirtableClient;

class AirtableClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AirtableClient::class, function ($app) {
            return new AirtableClient();
        });
    }
}
