<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

class SentryRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        Integration::configureScope(function (Scope $scope) use ($request): void {
            $scope->setTag('route', $request->route()?->getName() ?? 'unknown');
            $scope->setTag('method', $request->method());
            $scope->setContext('request', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            if (auth()->check()) {
                $scope->setUser([
                    'id' => (string) auth()->id(),
                    'email' => auth()->user()->email,
                ]);
            }
        });

        return $next($request);
    }
}
