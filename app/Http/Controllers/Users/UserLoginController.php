<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Users\UserLoginRequest;

class UserLoginController extends Controller
{
    private UserLoginRequest $globalRequestObject;
    private User|null $user;


    private function prepareRefreshToken(): string
    {
        $refreshTokenPayload = [
            "iat" => time(),
            "exp" => time() + 2592000, // 1 mois
            "userData" => array(
                "userId" => $this->user->id,
            )
        ];

        $refreshTokenObject = new JWTService($refreshTokenPayload);

        $refreshToken = $refreshTokenObject->getJwtToken();

        $this->user->refreshToken = Hash::make($refreshToken);
        $this->user->lastLogin = now();

        try {
            $updated = $this->user->save();
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
                "userId" => $this->user->id,
            )
        ];

        $accessTokenObject = new JWTService($accessTokenPayload);
        return $accessTokenObject->getJwtToken();
    }


    private function checkUserValidity()
    {
        if (!$this->user->isActive) {
            throw new Exception(
                "The logged in user has been suspended.",
                403
            );
        }
        if ($this->user->emailVerificationToken) {
            throw new Exception(
                "The email address has not been verified.",
                403
            );
        }
        if ($this->user->passwordResetToken) {
            throw new Exception(
                "A password reset request has been made, but it hasn't been finalized.",
                403
            );
        }
        if ($this->user->refreshToken) {
            throw new Exception(
                "The user is already logged in.",
                403
            );
        }
    }


    private function checkUserPassword()
    {
        if (
            !Hash::check(
                $this->globalRequestObject->input("password"),
                $this->user->password
            )
        ) {
            throw new Exception('Invalid email or password.', 401);
        }
    }


    private function loadUserFromDatabase()
    {
        try {
            $this->user = User::where("email", $this->globalRequestObject->input("email"))->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->user) {
            throw new Exception('Invalid email or password.', 401);
        }
    }

    
    public function __invoke(UserLoginRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->loadUserFromDatabase();

        $this->checkUserPassword();

        $this->checkUserValidity();

        $accessToken = $this->prepareAccessToken();

        $refreshToken = $this->prepareRefreshToken();

        return response()->json([
            'message' => 'User logged in successfully!',
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken
        ], status: 200);
    }
}
