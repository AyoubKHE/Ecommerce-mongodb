<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetUserByIdController extends Controller
{
    private Request $globalRequestObject;
    private User|null $loggedInUser;
    private User|null $requestedUser;

    private function loadRequestedUserFromDatabase()
    {
        $this->requestedUser = User::where(
            "id",
            $this->globalRequestObject->userId
        )->first();

        if (!$this->requestedUser) {
            throw new Exception('Requested user not found.', 404);
        }
    }

    private function checkLoggedInUserPermissions()
    {

        if ($this->loggedInUser->id === $this->globalRequestObject->userId) {
            return;
        }

        if ($this->loggedInUser->role === "Admin") {
            return;
        }

        foreach ($this->loggedInUser->permissions as $permission) {
            if ($permission['name'] === 'Utilisateurs') {
                if ((int) $permission['value'] === -1 || ((int) $permission['value'] & 1) === 1) {
                    return;
                } else {
                    throw new Exception(
                        "Access denied: You do not have the necessary permissions to perform this action.",
                        403
                    );
                }
            }
        }
    }

    private function loadLoggedInUserFromDatabase()
    {
        try {
            $this->loggedInUser = User::where(
                "id",
                $this->globalRequestObject->get('loggedInUserId')
            )->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->loggedInUser) {
            throw new Exception('Logged in user not found.', 404);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->loadLoggedInUserFromDatabase();

        $this->checkLoggedInUserPermissions();

        $this->loadRequestedUserFromDatabase();

        return response()->json([
            'user' => $this->requestedUser,
        ], 200);
    }
}
