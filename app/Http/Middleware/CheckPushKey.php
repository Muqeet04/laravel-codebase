<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPushKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pushKey = $request->header('x-push-key') ?: $request->input('push_key');
        $expectedKey = env('PUSH_API_KEY', 'super-secret-push-key-2026');

        if (!$pushKey || $pushKey !== $expectedKey) {
            return response()->json(['detail' => 'Invalid or missing X-Push-Key'], 401);
        }

        return $next($request);
    }
}
