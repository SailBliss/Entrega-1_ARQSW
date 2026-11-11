<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $previous = app()->getLocale();
        $locale = $request->cookie('locale', 'es');
        $locale = in_array($locale, ['es', 'en'], true) ? $locale : 'es';
        $request->setLocale($locale);
        app()->setLocale($locale);

        try {
            return $next($request);
        } finally {
            app()->setLocale($previous);
        }
    }
}
