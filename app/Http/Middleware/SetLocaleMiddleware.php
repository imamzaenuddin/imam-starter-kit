<?php

namespace App\Http\Middleware;

use App\Services\BahasaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        app(BahasaService::class)->terapkanLocale($request);

        return $next($request);
    }
}
