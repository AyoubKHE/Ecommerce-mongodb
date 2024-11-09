<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RefreshUserAccessTokenController extends Controller
{
    private Request $globalRequestObject;
    private User|null $loggedInUser;

    private function prepareRefreshToken(): string
    {
        $refreshTokenPayload = [
            "iat" => time(),
            "exp" => time() + 2592000, // 1 mois
            "userData" => array(
                "userId" => $this->loggedInUser->id,
            )
        ];

        $refreshTokenObject = new JWTService($refreshTokenPayload);

        $refreshToken = $refreshTokenObject->getJwtToken();

        $this->loggedInUser->refreshToken = Hash::make($refreshToken);

        try {
            $updated = $this->loggedInUser->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$updated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        return $refreshToken;
    }


    private function prepareAccessToken(): string
    {
        $accessTokenPayload = [
            "iat" => time(),
            "exp" => time() + 2592000, // 900 ==> 15 minutes
            "userData" => array(
                "userId" => $this->loggedInUser->id,
            )
        ];

        $accessTokenObject = new JWTService($accessTokenPayload);
        return $accessTokenObject->getJwtToken();
    }

    private function checkRefreshTokenValidity()
    {
        try {

            JWTService::checkTokenValidity(
                $this->globalRequestObject->refreshToken
            );

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The refresh token has expired. Authentication required.", 401);

            } else {
                throw new Exception("The refresh token is invalid. Authentication required.", 401);
            }
        }

        if (
            !Hash::check(
                $this->globalRequestObject->refreshToken,
                $this->loggedInUser->refreshToken
            )
        ) {
            throw new Exception("The refresh token is invalid. Authentication required.", 400);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->loggedInUser = $request->get('loggedInUser');

        $this->checkRefreshTokenValidity();

        $accessToken = $this->prepareAccessToken();

        $refreshToken = $this->prepareRefreshToken();

        return response()->json([
            'message' => 'You have refreshed your access token!',
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ], status: 200);
    }
}
