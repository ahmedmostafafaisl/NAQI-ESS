<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->header('Accept-Language', config('app.locale')));

        return $next($request);
    }
}
