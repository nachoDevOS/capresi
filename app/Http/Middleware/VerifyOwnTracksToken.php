<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyOwnTracksToken
{
    public function handle(Request $request, Closure $next)
    {
        $authToken = $request->header('X-Limit-U') ?? $request->input('u');
        $expectedToken = config('services.owntracks.token');

        if ($authToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
