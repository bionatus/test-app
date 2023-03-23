<?php

namespace App\Providers;

use App;
use App\Services\Communication\Auth;
use App\Services\CustomerSupport\Call;
use App\Services\CustomerSupport\Providers\Twilio\SupportCall\Response as SupportResponse;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class CustomerSupportProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
        $this->app->bind(Call\ResponseInterface::class, SupportResponse::class);
        $this->app->bind(Auth\Call\ResponseInterface::class, Auth\Providers\Twilio\Call\Response::class);
    }
}
