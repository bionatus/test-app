<?php

namespace App\Providers;

use App\Constants\RelationsMorphs;
use App\Services\Nutshell\Nutshell;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use NutshellApi;
use Str;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Nutshell::class, function() {
            $username = env('NUTSHELL_API_USER');
            $apiKey   = env('NUTSHELL_API_KEY');

            return new Nutshell(new NutshellApi($username, $apiKey));
        });
    }

    public function boot()
    {
        Relation::morphMap(RelationsMorphs::MAP);

        Relation::macro('getAliasByModel', function(string $modelClass) {
            return array_search($modelClass, Relation::morphMap());
        });

        Str::macro('uuidFromString',
            fn($string) => strtoupper(substr_replace(substr_replace(vsprintf('%s%s-%s-%s-%s-%s%s%s',
                str_split(md5($string), 4)), '4', 14, 1), 'A', 19, 1)));
    }
}
