<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;


class JWTAuthentication
{

    private Request $request;

    //! PRIVATE Functions-------------------------------------------------------------------------------------------------------------------

    private function checkRefreshTokenValidity(): bool|JsonResponse
    {

        JWTService::checkTokenValidity($this->request->cookie("refresh_token"));
        $refresh_token_payload = JWTService::getTokenPayload($this->request->cookie("refresh_token"));

        return true;
    }


    private function checkAccessTokenSource(): array|JsonResponse
    {

        $access_token_payload = JWTService::getTokenPayload($this->request->cookie('access_token'));

        return $access_token_payload;
    }


    private function buildNewAccessToken(array $access_token_payload): void
    {

        $access_token_payload["iat"] = time();
        $access_token_payload["nbf"] = time();
        $access_token_payload["exp"] = time() + 600; // 10 minutes

        $jwt = new JWTService($access_token_payload);

        $refreshed_token = $jwt->getJwtToken();

        $this->request->headers->set('refreshed_token', $refreshed_token);
    }


    private function manageExpiredToken(Exception $expiredTokenException): bool|JsonResponse
    {


        if ($this->request->cookie("refresh_token") !== null) {

            try {

                $checkRefreshTokenValidity_FunctionResult = $this->checkRefreshTokenValidity();
                if ($checkRefreshTokenValidity_FunctionResult instanceof JsonResponse) {
                    return $checkRefreshTokenValidity_FunctionResult;
                }

                $checkAccessTokenValidity_FunctionResult = $this->checkAccessTokenSource();
                if ($checkAccessTokenValidity_FunctionResult instanceof JsonResponse) {
                    return $checkAccessTokenValidity_FunctionResult;
                }

                $access_token_payload = $checkAccessTokenValidity_FunctionResult;

                if (!$this->isUserAuthorized($access_token_payload)) {
                    return response()->json(["success" => false], 403);
                }

                $this->request->headers->set("current_user_id", $access_token_payload["user_data"]->user_id);

                $this->buildNewAccessToken($access_token_payload);
            } catch (Exception $e) {
                return response()->json(["message" => $e->getMessage(), "success" => false], 401); // unauthenticated
            }
        } else {
            return response()->json(["message" => $expiredTokenException->getMessage(), "success" => false], 401); // unauthenticated
        }

        return true;
    }


    private function isUserAuthorized(array $access_token_payload): bool
    {
        $user = User::find($access_token_payload["user_data"]->user_id);

        return $user->can($this->request->input("ability"), [$user]);
    }
    //! -------------------------------------------------------------------------------------------------------------------------------------



    //! PUBLIC Functions---------------------------------------------------------------------------------------------------------------------

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {

        if ($request->cookie("access_token") === null) {
            return response()->json(["success" => false], 401); // unauthenticated
        }

        try {

            $access_token_payload = JWTService::checkTokenValidity($request->cookie("access_token"));

            $this->request = $request;

            if (!$this->isUserAuthorized($access_token_payload)) {
                return response()->json(["success" => false], 403);
            }

            $request->headers->set("current_user_id", $access_token_payload["user_data"]->user_id);


        } catch (\Exception $e) {

            if ($e instanceof ExpiredException) {

                $this->request = $request;

                $manageExpiredToken_FunctionResult = $this->manageExpiredToken($e);
                if ($manageExpiredToken_FunctionResult instanceof JsonResponse) {
                    return $manageExpiredToken_FunctionResult;
                }
            } else {
                return response()->json(["message" => $e->getMessage(), "success" => false], 401);
            }
        }

        $newToken = $request->header('refreshed_token');
        if ($newToken !== null) {

            /** @var \Illuminate\Http\Response $response */

            $response = $next($request);

            return $response->withCookie(cookie("access_token", $newToken));
        } else {
            return $next($request);
        }
    }

    //! -------------------------------------------------------------------------------------------------------------------------------------

}
