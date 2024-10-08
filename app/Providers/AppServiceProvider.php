<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Request::macro(
            'allFilled',
            function (array $keys) {
                foreach ($keys as $key) {
                    if (! $this->filled($key)) {
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
