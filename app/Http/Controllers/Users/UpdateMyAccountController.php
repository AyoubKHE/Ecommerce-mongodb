<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateMyAccountRequest;

class UpdateMyAccountController extends Controller
{
    private UpdateMyAccountRequest $globalRequestObject;
    private array $sentInputs;
    private User $loggedInUser;

    private function isSuperAdminUpdatedHisFirstNameOrLastNameOrUsername()
    {
        return Arr::has($this->sentInputs, 'firstName') ||
            Arr::has($this->sentInputs, 'lastName') ||
            Arr::has($this->sentInputs, 'username');
    }

    private function updateCreatedByAndUpdatedByAndDeletedByOfOtherUsers()
    {
        if (
            $this->loggedInUser->role === "Super Admin" &&
            $this->isSuperAdminUpdatedHisFirstNameOrLastNameOrUsername()
        ) {

            try {
                $allUsers = User::withTrashed()->where('id', '!=', $this->loggedInUser->id)->get();

            } catch (Throwable $th) {
                throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
            }

            foreach ($allUsers as $user) {
                $user->createdBy = [
                    "id" => $user->createdBy['id'],
                    "firstName" => $this->loggedInUser->firstName,
                    "lastName" => $this->loggedInUser->lastName,
                    "username" => $this->loggedInUser->username,
                    "email" => $user->createdBy['email'],
                ];

                if ($user->updatedBy && $user->updatedBy['id']->__toString() === $this->loggedInUser->id) {
                    $user->updatedBy = [
                        "id" => $user->updatedBy['id'],
                        "firstName" => $this->loggedInUser->firstName,
                        "lastName" => $this->loggedInUser->lastName,
                        "username" => $this->loggedInUser->username,
                        "email" => $user->updatedBy['email'],
                    ];
                }

                if ($user->deletedBy) {
                    $user->deletedBy = [
                        "id" => $user->deletedBy['id'],
                        "firstName" => $this->loggedInUser->firstName,
                        "lastName" => $this->loggedInUser->lastName,
                        "username" => $this->loggedInUser->username,
                        "email" => $user->deletedBy['email'],
                    ];
                }


                try {
                    $isUpdated = $user->save();
                } catch (Throwable $throwable) {
                    throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
                }

                if (!$isUpdated) {
                    throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
                }
            }
        }
    }

    private function prepareUpdatedBy()
    {
        $this->sentInputs['updatedBy'] = [
            "id" => new ObjectId($this->loggedInUser->id),
            "firstName" => Arr::has($this->sentInputs, 'firstName') ?
                $this->sentInputs['firstName'] :
                $this->loggedInUser->firstName,
            "lastName" => Arr::has($this->sentInputs, 'lastName') ?
                $this->sentInputs['lastName'] :
                $this->loggedInUser->lastName,
            "username" => Arr::has($this->sentInputs, 'username') ?
                $this->sentInputs['username'] :
                $this->loggedInUser->username,
            "email" => $this->loggedInUser->email,
        ];
    }

    private function updateMyAccount()
    {
        $this->prepareUpdatedBy();

        $this->sentInputs["updatedAt"] = now();

        try {
            $isUpdated = $this->loggedInUser->update($this->sentInputs);
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    private function checkModificationsAreMade()
    {
        $this->loggedInUser = $this->globalRequestObject->get('loggedInUser');

        $this->sentInputs = $this->globalRequestObject->validated();

        $originalData = $this->loggedInUser->getOriginal();

        foreach ($this->sentInputs as $key => $value) {
            if ($value === null || $value == $originalData[$key]) {
                unset($this->sentInputs[$key]);
            }
        }

        if (count($this->sentInputs) === 0) {
            throw new Exception(
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }
    }

    public function __invoke(UpdateMyAccountRequest $request)
    {
        $this->globalRequestObject = $request;

        $this->checkModificationsAreMade();

        DB::transaction(function () {

            $this->updateMyAccount();

            $this->updateCreatedByAndUpdatedByAndDeletedByOfOtherUsers();

        });

        return response()->json([
            'message' => "User's account updated successfully!",
        ], 200);
    }
}
