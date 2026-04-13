<?php

namespace App\Http\Middleware;

use App\Services\LogAktivitasService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CatatAktivitasMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userSebelum = $request->user();
        $response = $next($request);

        app(LogAktivitasService::class)->catatDariRequest($request, $response, $userSebelum);

        return $response;
    }
}
