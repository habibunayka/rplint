<?php

namespace App\Http\Middleware;

use Closure;

class CustomBearerAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
         

        if ($token !== config('custom.api_token')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
