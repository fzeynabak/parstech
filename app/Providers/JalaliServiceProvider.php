<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Morilog\Jalali\Jalalian;

class JalaliServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if (!function_exists('jdate')) {
            function jdate($date = null)
            {
                return Jalalian::fromCarbon($date);
            }
        }
    }
}
