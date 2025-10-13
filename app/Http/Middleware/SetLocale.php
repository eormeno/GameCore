<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);
        App::setLocale($locale);
        
        return $next($request);
    }
    
    /**
     * Determine the locale for the request
     */
    private function getLocale(Request $request): string
    {
        // 1. Check if authenticated user has locale preference
        if (Auth::check() && Auth::user()->locale) {
            $userLocale = Auth::user()->locale;
            if (in_array($userLocale, config('app.available_locales', ['en', 'es']))) {
                return $userLocale;
            }
        }
        
        // 2. Check for locale in request parameters (for API calls)
        if ($request->has('locale')) {
            $requestLocale = $request->get('locale');
            if (in_array($requestLocale, config('app.available_locales', ['en', 'es']))) {
                return $requestLocale;
            }
        }
        
        // 3. Check Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $preferredLocale = substr($acceptLanguage, 0, 2);
            if (in_array($preferredLocale, config('app.available_locales', ['en', 'es']))) {
                return $preferredLocale;
            }
        }
        
        // 4. Use default locale
        return config('app.locale', 'en');
    }
}