<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNoTokenForGuest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() && $request->bearerToken() != "null") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You are already authenticated, registration or login is not allowed.',
                'data' => null
            ], 400);
        }

        return $next($request);
    }
}
