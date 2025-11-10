<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $allowedYear = env('SUBSCRIPTION_YEAR');
        $currentYear = Carbon::now()->year;

        //if ($currentYear > $allowedYear) {
            // Stop execution and show subscription page
            //echo view('subscription.expired');
           // exit; // prevent rest of app from running
       // }
    }
}
