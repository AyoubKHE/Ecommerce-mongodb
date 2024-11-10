<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserPasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Users\UserForgetPasswordRequest;

class UserForgetPasswordController extends Controller
{

    private UserForgetPasswordRequest $globalRequestObject;
    private User|null $user;
    private string $passwordResetToken;


    private function preparePasswordResetToken(): void
    {
        $passwordResetTokenPayload = [
            "iat" => time(),
            "exp" => time() + 900, // 5 minutes
            "userData" => array(
                "userId" => $this->user->id,
            )
        ];

        $passwordResetTokenObject = new JWTService($passwordResetTokenPayload);

        $this->passwordResetToken = $passwordResetTokenObject->getJwtToken();

        $this->user->passwordResetToken = Hash::make($this->passwordResetToken);
    }

    private function addPasswordResetTokenToUserDocument(): void
    {
        $this->preparePasswordResetToken();

        try {
            $isUpdated = $this->user->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function sendPasswordResetLink(): void
    {
        try {
            Mail::to($this->user->email)->send(new UserPasswordReset($this->user->firstName, $this->passwordResetToken));
        } catch (Throwable $throwable) {
            throw new Exception('Unable to send the password reset link. Please check the user email address and try again.', 500);
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
            throw new Exception('User not found', 404);
        }
    }


    public function __invoke(UserForgetPasswordRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->loadUserFromDatabase();

        DB::transaction(function () {

            $this->addPasswordResetTokenToUserDocument();

            $this->sendPasswordResetLink();
        });

        return response()->json([
            'message' => 'The password reset link has been sent to the user email. The link is valid for 5 minutes only.'
        ], status: 200);
    }
}
