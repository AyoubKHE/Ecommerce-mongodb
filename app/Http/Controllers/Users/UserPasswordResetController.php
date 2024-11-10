<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Users\UserResetPasswordRequest;

class UserPasswordResetController extends Controller
{

    private UserResetPasswordRequest $globalRequestObject;
    private User|null $user;

    private function updateUserDocumentInDatabase()
    {
        $this->user->password = Hash::make(
            $this->globalRequestObject->input("newPassword")
        );
        $this->user->passwordResetToken = null;
        $this->updatedAt = now();

        try {
            $isUpdated = $this->user->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function passwordResetTokenValidation()
    {
        try {
            $passwordResetTokenPayload = JWTService::checkTokenValidity(
                $this->globalRequestObject->input("passwordResetToken")
            );

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The password reset token has expired.", 400);

            } else {
                throw new Exception("The password reset token is invalid.", 400);
            }
        }

        try {
            $this->user = User::where("id", $passwordResetTokenPayload["userData"]->userId)->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->user) {
            throw new Exception('User not found', 404);
        }

        if (!$this->user->passwordResetToken) {
            throw new Exception('The user has not requested a token to reset the password.', 400);
        }

        //! Cette vérification protège notre système en empêchant les entités non autorisées de générer des tokens de validation, même si elles ont accès à 'JWT_SECRET'. 
        //! En effet, sans les claims 'iat' (issued at) et 'exp' (expiration), tout token généré sera différent et donc invalide.

        //! Un exemple: Moi je suis un pirate et j'ai access au "JWT_SECRET" je peux utiliser n'importe qu'elle
        //! addresse email et vu que j'ai pas access à cette boite email donc je peux pas avoir le token envoyé.
        //! mais je peux créer un token moi meme avec le "JWT_SECRET", il sera valide ! mais il sera surement different
        //! de token envoyé à la boite email car j'ai pas le "_id", le "iat" et le "exp" utilsé dans le token envoyé
        //! donc le token sera different.
        //! donc ce check est logique car sans lui je peux générer un token valide et je passe. meme si il est
        //! different de celui envoyé mais il reste un token générer pas le "JWT_SECRET" donc il est valide.
        if (
            !Hash::check(
                $this->globalRequestObject->input("passwordResetToken"),
                $this->user->passwordResetToken
            )
        ) {
            throw new Exception("The password reset token is invalid.", 400);
        }

    }

    public function __invoke(UserResetPasswordRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->passwordResetTokenValidation();

        $this->updateUserDocumentInDatabase();

        return response()->json([
            'message' => "User's password has been reset successfully!"
        ], 200);
    }
}
