<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Hekmatinasser\Verta\Verta;

class VertaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('verta', function ($app) {
            return new Verta();
        });
    }

    public function boot()
    {
        //
    }
}
