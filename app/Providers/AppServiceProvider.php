<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Carbon::setLocale('pt_BR');  // Defina a localidade para o Brasil
        date_default_timezone_set('America/Sao_Paulo');  // Configura o fuso horário para o Brasil
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
