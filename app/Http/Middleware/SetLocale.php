<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, ['fr', 'en', 'de', 'es', 'it', 'nl'])) {
                App::setLocale($locale);
            }
        } else {
            // Default locale
            App::setLocale('fr');
        }

        return $next($request);
    }
}
