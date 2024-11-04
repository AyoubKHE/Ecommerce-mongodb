<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Services\JWTService;
use Illuminate\Http\Request;


class JWTAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        $bearerToken = $request->header('Authorization');

        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            throw new Exception("Authorization token is missing or invalid format.", 400);
        }

        $accessToken = substr($bearerToken, 7);

        try {

            $accessTokenPayload = JWTService::checkTokenValidity($accessToken);

            $request->attributes->set('userId', $accessTokenPayload["userData"]->userId);

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The access token has expired.", 401);

            } else {
                throw new Exception("The access token is invalid.", 401);
            }
        }

        return $next($request);
    }

}
