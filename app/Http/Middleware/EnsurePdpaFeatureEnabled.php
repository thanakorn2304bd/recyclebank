<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnsurePdpaFeatureEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        throw_unless((bool) config('features.pdpa', false), new NotFoundHttpException);

        return $next($request);
    }
}
