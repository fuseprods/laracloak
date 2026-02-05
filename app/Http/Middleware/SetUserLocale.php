<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. URL Parameter Priority (and persistence)
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if (in_array($lang, ['en', 'es'])) {
                $locale = $lang;
                session(['locale' => $locale]);

                if (auth()->check()) {
                    auth()->user()->update(['locale' => $locale]);
                }
            }
        }

        // 2. User Profile Priority
        if (!$locale && auth()->check() && !empty(auth()->user()->locale)) {
            $locale = auth()->user()->locale;
        }

        // 3. Session Priority
        if (!$locale && session()->has('locale')) {
            $locale = session('locale');
        }

        // 4. Browser Fallback
        if (!$locale) {
            $locale = $this->detectBrowserLocale($request);
        }

        app()->setLocale($locale);

        return $next($request);
    }

    protected function detectBrowserLocale(Request $request): string
    {
        $browserLocale = $request->getPreferredLanguage(['en', 'es']);

        return $browserLocale ?: config('app.locale');
    }
}
