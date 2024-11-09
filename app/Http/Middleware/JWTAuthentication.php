<?php

namespace App\Http\Middleware;

use Throwable;
use Closure;
use Exception;

use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\Request;


class JWTAuthentication
{
    private Request $globalRequestObject;
    private string $loggedInUserId;

    private function loadLoggedInUser()
    {
        try {
            $loggedInUser = User::where("id", $this->loggedInUserId)->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$loggedInUser) {
            throw new Exception('Logged in user not found.', 404);
        }

        if (!$loggedInUser->isActive) {
            throw new Exception("The logged in user has been suspended.", 403);
        }

        $this->globalRequestObject->attributes->set('loggedInUser', $loggedInUser);
    }

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

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The access token has expired. Please request a new access token using the refresh token.", 401);

            } else {
                throw new Exception("The access token is invalid. Authentication required.", 401);
            }
        }

        $this->loggedInUserId = $accessTokenPayload["userData"]->userId;

        $this->globalRequestObject = $request;

        $this->loadLoggedInUser();

        return $next($request);
    }

}
