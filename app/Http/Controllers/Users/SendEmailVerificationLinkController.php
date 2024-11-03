<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserEmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Users\sendEmailVerificationLinkRequest;

class SendEmailVerificationLinkController extends Controller
{

    private sendEmailVerificationLinkRequest $globalRequestObject;
    private User $user;
    private string $emailVerificationToken;


    private function prepareEmailVerificationToken(): void
    {
        $emailVerificationTokenPayload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "userData" => array(
                "userId" => $this->user->id,
            )
        ];

        $emailVerificationTokenObject = new JWTService($emailVerificationTokenPayload);

        $this->emailVerificationToken = $emailVerificationTokenObject->getJwtToken();

        $this->user->emailVerificationToken = Hash::make($this->emailVerificationToken);
    }

    private function addEmailVerificationTokenToUserDocument(): void
    {
        $this->prepareEmailVerificationToken();

        try {
            $this->user->save();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function sendEmailVerificationLink(): void
    {
        try {
            Mail::to($this->user->email)->send(new UserEmailVerification($this->user->firstName, $this->emailVerificationToken));
        } catch (Throwable $throwable) {
            throw new Exception('Unable to send the confirmation email. Please check the user email address and try again.', 500);
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
    

    public function __invoke(sendEmailVerificationLinkRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->loadUserFromDatabase();

        DB::transaction(function () {

            $this->addEmailVerificationTokenToUserDocument();

            $this->sendEmailVerificationLink();
        });

        return response()->json([
            'message' => 'The confirmation link has been sent to the user email. The confirmation link is valid for 15 minutes only.'
        ], status: 200);
    }
}
