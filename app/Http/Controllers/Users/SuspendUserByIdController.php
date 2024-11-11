<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuspendUserByIdController extends Controller
{
    private Request $globalRequestObject;
    private User|null $requestedUser;

    private function prepareUpdatedBy()
    {
        $loggedInUser = $this->globalRequestObject->get('loggedInUser');

        $this->requestedUser['updatedBy'] = [
            "id" => new ObjectId($loggedInUser->id),
            "firstName" => $loggedInUser->firstName,
            "lastName" => $loggedInUser->lastName,
            "username" => $loggedInUser->username,
            "email" => $loggedInUser->email,
        ];
    }

    private function suspendRequestedUser()
    {
        $this->requestedUser->isActive = false;
        $this->requestedUser->updatedAt = now();

        $this->prepareUpdatedBy();

        try {
            $isUpdated = $this->requestedUser->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function loadRequestedUser()
    {
        $this->requestedUser = User::where(
            "id",
            $this->globalRequestObject->userId
        )->first();

        if (!$this->requestedUser) {
            throw new Exception('Requested user not found.', 404);
        }

        if (!$this->requestedUser->isActive) {
            throw new Exception('Requested user already suspended.', 403);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->loadRequestedUser();

        $this->suspendRequestedUser();

        return response()->json([
            'message' => 'User suspended successfully.',
        ], 200);
    }
}
