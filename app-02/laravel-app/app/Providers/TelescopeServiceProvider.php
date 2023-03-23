<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filterBatch(function (Collection $entries) {
            if (Config::get('telescope.record_all_data')) {
                return true;
            }

            return $entries->contains(function ($entry) {
                return $entry->isReportableException() || $entry->isFailedJob() || $entry->isScheduledTask() || $entry->hasMonitoredTag();
            });
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->isLocal()) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, Config::get('telescope.allowed_users'));
        });
    }
}
