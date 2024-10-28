<?php
namespace App\Services\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserEmailConfirmation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Users\FirstAdminRegistrationRequest;

class clsFirstAdminRegistration
{
    private FirstAdminRegistrationRequest $globalRequestObject;
    private array $user;
    private bool $isUserImageFolderCreated = false;
    private string $emailVerificationToken;


    private function prepareEmailVerificationToken(): void
    {
        $emailVerificationTokenPayload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "userData" => array(
                "userId" => $this->user['_id']->__toString(),
            )
        ];

        $emailVerificationTokenObject = new JWTService($emailVerificationTokenPayload);

        $this->emailVerificationToken = $emailVerificationTokenObject->getJwtToken();

        $this->user['emailVerificationToken'] = Hash::make($this->emailVerificationToken);
    }

    private function preparingData(): void
    {
        $this->user = $this->globalRequestObject->validated();

        $this->user['_id'] = new ObjectId();

        $this->user['password'] = Hash::make($this->user['password']);

        $this->user['isActive'] = true;

        $this->prepareEmailVerificationToken();

        $this->user['passwordResetToken'] = null;

        $this->user['refreshToken'] = null;

        $this->user['role'] = "Admin";

        $this->user['permissions'] = null;

        $this->user['lastLogin'] = null;

        $this->user['deletedAt'] = null;

        $this->user['createdBy'] = null;

        $this->user['updatedBy'] = null;

        $this->user['deletedBy'] = null;


    }

    private function storeUserProfileImage(): void
    {
        $imageFile = $this->globalRequestObject->file('profileImage');
        $folderPath = 'users/id_' . $this->user['_id']->__toString();

        $this->user["profileImagePath"] = $imageFile->store($folderPath, 'public');
        if (!$this->user["profileImagePath"]) {
            throw new Exception("An error occurred while saving the user's profile image.", 500);
        }

        $this->isUserImageFolderCreated = true;

        unset($this->user["profileImage"]);
    }

    private function storeUser(): void
    {
        try {
            User::create($this->user);

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function sendEmailVerificationLink(): void
    {
        try {
            Mail::to($this->user['email'])->send(new UserEmailConfirmation($this->user['firstName'], $this->emailVerificationToken));
        } catch (Throwable $throwable) {
            throw new Exception('Unable to send the confirmation email. Please check the user email address and try again.', 500);
        }
    }

    private function isFirstAdmin(): void
    {
        try {
            $usersCount = User::count();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if ($usersCount > 0) {
            throw new Exception('Access denied. Only the first admin can register through this endpoint.', 403);
        }
    }

    public function main(FirstAdminRegistrationRequest $request): JsonResponse
    {
        $this->globalRequestObject = $request;

        $this->isFirstAdmin();

        $this->preparingData();

        try {

            DB::transaction(function () {

                $this->storeUserProfileImage();

                $this->storeUser();

                $this->sendEmailVerificationLink();
            });

            return response()->json([
                'message' => 'User Account is created successfully. An email confirmation has been sent to the user. The confirmation link is valid for 15 minutes only.'
            ], 201);

        } catch (Throwable $throwable) {

            if ($this->isUserImageFolderCreated) {
                Storage::deleteDirectory("public/users/id_" . $this->user['_id']->__toString());
            }

            throw $throwable;
        }
    }
}