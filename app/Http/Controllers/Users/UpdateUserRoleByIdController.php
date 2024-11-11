<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserRoleRequest;

class UpdateUserRoleByIdController extends Controller
{

    private UpdateUserRoleRequest $globalRequestObject;
    private User|null $requestedUser;
    private array $sentInputs;

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

    private function updateUserRole()
    {
        $this->requestedUser->role = $this->sentInputs['role'];
        $this->requestedUser->permissions = $this->sentInputs['permissions'];
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

    private function checkModificationsAreMade()
    {
        $this->sentInputs = $this->globalRequestObject->validated();

        if ($this->requestedUser->role === "Admin") {

            if ($this->sentInputs['role'] === "Admin") {
                throw new Exception(
                    'No updates were made. Please ensure there is at least one modification before submitting.',
                    400
                );
            }

        } else {
            if ($this->sentInputs['role'] === "User") {
                foreach ($this->requestedUser->permissions as $requestedUserPermission) {
                    foreach ($this->sentInputs['permissions'] as $sentPermission) {
                        if ($requestedUserPermission["name"] === $sentPermission["name"]) {
                            if (((int) $requestedUserPermission["value"]) !== $sentPermission["value"]) {
                                return;
                            } else {
                                break;
                            }
                        }
                    }
                }

                throw new Exception(
                    'No updates were made. Please ensure there is at least one modification before submitting.',
                    400
                );
            } else {
                $this->sentInputs['permissions'] = null;
            }
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
            throw new Exception('Requested user is suspended.', 403);
        }
    }

    public function __invoke(UpdateUserRoleRequest $request)
    {
        $this->globalRequestObject = $request;

        $this->loadRequestedUser();

        $this->checkModificationsAreMade();

        $this->updateUserRole();

        return response()->json([
            'message' => "User's role updated successfully.",
        ], 200);
    }
}
