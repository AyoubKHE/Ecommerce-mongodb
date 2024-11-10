<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $loggedInUser = $request->get('loggedInUser');
        if ($loggedInUser->role === "Super Admin") {
            return $next($request);
        } else {
            throw new Exception(
                "Access denied: User do not have the necessary permissions to perform this action.",
                403
            );
        }
    }
}
