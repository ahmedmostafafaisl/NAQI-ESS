<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales the application actually supports. Adjust as needed.
     */
    protected array $supportedLocales = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    /**
     * Pick a supported locale from an explicit ?lang= param, the user's
     * stored preference, or the Accept-Language header — falling back to
     * the app default. Never trusts the raw header value directly.
     */
    protected function resolveLocale(Request $request): string
    {
        $requested = $request->query('lang');

        if ($requested && $this->isSupported($requested)) {
            return $requested;
        }

        foreach ($this->parseAcceptLanguage($request->header('Accept-Language', '')) as $locale) {
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        return config('app.locale');
    }

    /**
     * Parse an "Accept-Language" header into an ordered list of short
     * locale codes, e.g. "en_EG,en;q=0.9,ar;q=0.8" -> ['en', 'en', 'ar'].
     */
    protected function parseAcceptLanguage(string $header): array
    {
        if ($header === '') {
            return [];
        }

        $locales = [];

        foreach (explode(',', $header) as $part) {
            // Strip the ";q=0.9" quality suffix, if present.
            $tag = trim(explode(';', $part)[0] ?? '');

            if ($tag === '') {
                continue;
            }

            // "en_EG" / "en-EG" -> "en"
            $primary = strtolower(preg_split('/[-_]/', $tag)[0] ?? '');

            if ($primary !== '') {
                $locales[] = $primary;
            }
        }

        return $locales;
    }

    protected function isSupported(string $locale): bool
    {
        return in_array(strtolower($locale), $this->supportedLocales, true);
    }
}
