<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;
use TCG\Voyager\Facades\Voyager;
use App\FormFields\register_userIdFormField;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Voyager::addFormField(register_userIdFormField::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Voyager::addAction(\App\Actions\AddPaymentEmploye::class);


        View::composer('*', function ($view) {
            $global_cashier = new Controller();
            $global_cashier = $global_cashier->availableMoney(Auth::user()?Auth::user()->id:null, 'user');
            // $global_cashier = $global_cashier->availableMoney(Auth::user()->id, 'user');
            $view->with('global_cashier', $global_cashier->original); //Para retornar en formato json
            // $view->with('global_cashier', $global_cashier); //Para retornar en formato de array
        });

        // Detectar si estamos detrás de un proxy (como Coolify)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
