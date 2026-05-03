<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-Locale')
            ?? ($request->user()?->locale)
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (!in_array($locale, config('app.available_locales', ['pt-BR', 'en']))) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        Carbon::setLocale(str_replace('-', '_', $locale));

        return $next($request);
    }
}
