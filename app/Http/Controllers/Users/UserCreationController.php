<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Users\UserCreationRequest;

class UserCreationController extends Controller
{
    private UserCreationRequest $globalRequestObject;
    private array $preparedUser;
    private User|null $storedUser;
    private string $emailVerificationToken;


    private function prepareEmailVerificationToken(): void
    {
        $emailVerificationTokenPayload = [
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 900, // 15 minutes
            "userData" => array(
                "userId" => $this->preparedUser['_id']->__toString(),
            )
        ];

        $emailVerificationTokenObject = new JWTService($emailVerificationTokenPayload);

        $this->emailVerificationToken = $emailVerificationTokenObject->getJwtToken();

        $this->preparedUser['emailVerificationToken'] = Hash::make($this->emailVerificationToken);
    }

    private function prepareCreatedBy()
    {
        $loggedInUser = $this->globalRequestObject->get('loggedInUser');

        $this->preparedUser['createdBy'] = [
            "id" => new ObjectId($loggedInUser->id),
            "firstName" => $loggedInUser->firstName,
            "lastName" => $loggedInUser->lastName,
            "username" => $loggedInUser->username,
            "email" => $loggedInUser->email,
        ];
    }

    private function preparingData(): void
    {
        $this->preparedUser = $this->globalRequestObject->validated();

        $this->preparedUser['_id'] = new ObjectId();

        $this->preparedUser['password'] = Hash::make($this->preparedUser['password']);

        $this->preparedUser['isActive'] = true;

        $this->prepareEmailVerificationToken();

        $this->preparedUser['passwordResetToken'] = null;

        $this->preparedUser['refreshToken'] = null;

        if ($this->preparedUser['role'] === "Admin") {
            $this->preparedUser['permissions'] = null;
        }

        $this->preparedUser['lastLogin'] = null;

        $this->preparedUser['createdAt'] = now();
        
        $this->preparedUser['updatedAt'] = null;
        
        $this->preparedUser['deletedAt'] = null;

        $this->prepareCreatedBy();

        $this->preparedUser['updatedBy'] = null;

        $this->preparedUser['deletedBy'] = null;


    }

    private function storeUserProfileImage(): void
    {
        $imageFile = $this->globalRequestObject->file('profileImage');
        $folderPath = 'users/id_' . $this->preparedUser['_id']->__toString();

        $this->preparedUser["profileImagePath"] = $imageFile->store($folderPath, 'public');
        if (!$this->preparedUser["profileImagePath"]) {
            throw new Exception("An error occurred while saving the user's profile image.", 500);
        }

        unset($this->preparedUser["profileImage"]);
    }

    private function storeUser(): void
    {
        try {
            $this->storedUser = User::create($this->preparedUser);
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->storedUser) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function sendEmailVerificationLink(): void
    {
        try {
            Mail::to($this->preparedUser['email'])->send(new UserEmailVerification($this->preparedUser['firstName'], $this->emailVerificationToken));
        } catch (Throwable $throwable) {
            throw new Exception('Unable to send the confirmation email. Please check the user email address and try again.', 500);
        }
    }

    public function __invoke(UserCreationRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->preparingData();

        $this->storeUserProfileImage();

        try {

            DB::transaction(function () {
                $this->storeUser();

                $this->sendEmailVerificationLink();
            });

            return response()->json([
                'message' => 'User Account is created successfully. An email confirmation has been sent to the user. The confirmation link is valid for 15 minutes only.',
                'user' => $this->storedUser,
            ], 201);

        } catch (Throwable $throwable) {

            Storage::deleteDirectory("public/users/id_" . $this->preparedUser['_id']->__toString());

            throw $throwable;
        }
    }
}
