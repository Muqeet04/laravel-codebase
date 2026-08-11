<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDashboardAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['detail' => 'Not authenticated'], 401);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
