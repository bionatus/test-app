<?php

namespace App\Providers;

use App\Models\Point;
use App\Models\Staff;
use App\Models\SupplierUser;
use App\Models\Supply;
use App\Models\User as UserModel;
use App\Nova\ConversionJob;
use App\Nova\Observers\PointObserver;
use App\Nova\Observers\StaffObserver;
use App\Nova\Observers\SupplierUserObserver;
use App\Nova\Observers\SupplyObserver;
use App\Nova\Observers\UserObserver;
use App\Nova\Review;
use App\Nova\Store;
use App\Nova\Technician;
use App\Nova\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Observable;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot()
    {
        parent::boot();

        Observable::make(Point::class, PointObserver::class);
        Observable::make(SupplierUser::class, SupplierUserObserver::class);
        Observable::make(Supply::class, SupplyObserver::class);
        Observable::make(Staff::class, StaffObserver::class);
        Observable::make(UserModel::class, UserObserver::class);
    }

    protected function routes()
    {
        Nova::routes()->withAuthenticationRoutes()->withPasswordResetRoutes()->register();
    }

    protected function gate()
    {
        Gate::define('viewNova', function($user) {
            return $user->can('access nova');
        });
    }

    protected function cards()
    {
        return [
            new Help,
        ];
    }

    public function tools()
    {
        return [];
    }

    public function register()
    {
        //
    }

    protected function resources()
    {
        Nova::resourcesIn(app_path('Nova/Resources'));

        Nova::resources([
            ConversionJob::class,
            Review::class,
            Store::class,
            Technician::class,
            User::class,
        ]);
    }
}
