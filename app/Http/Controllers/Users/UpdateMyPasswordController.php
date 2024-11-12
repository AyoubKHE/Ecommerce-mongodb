<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Users\UpdateMyPasswordRequest;

class UpdateMyPasswordController extends Controller
{
    private UpdateMyPasswordRequest $globalRequestObject;
    private array $sentInputs;
    private User $loggedInUser;

    private function prepareUpdatedBy()
    {
        $this->loggedInUser->updatedBy = [
            "id" => new ObjectId($this->loggedInUser->id),
            "firstName" => $this->loggedInUser->firstName,
            "lastName" => $this->loggedInUser->lastName,
            "username" => $this->loggedInUser->username,
            "email" => $this->loggedInUser->email,
        ];
    }

    private function updateMyPassword()
    {
        $this->loggedInUser->password = Hash::make($this->sentInputs["newPassword"]);
        $this->loggedInUser->updatedAt = now();
        $this->prepareUpdatedBy();

        try {
            $isUpdated = $this->loggedInUser->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function checkOldPasswordValidity()
    {
        $this->loggedInUser = $this->globalRequestObject->get('loggedInUser');

        $this->sentInputs = $this->globalRequestObject->validated();

        if (
            !Hash::check(
                $this->sentInputs["oldPassword"],
                $this->loggedInUser->password
            )
        ) {
            throw new Exception('Invalid old password.', 403);
        }
    }

    public function __invoke(UpdateMyPasswordRequest $request)
    {
        $this->globalRequestObject = $request;

        $this->checkOldPasswordValidity();

        $this->updateMyPassword();

        return response()->json([
            'message' => "User's password updated successfully!",
        ], 200);

    }
}
