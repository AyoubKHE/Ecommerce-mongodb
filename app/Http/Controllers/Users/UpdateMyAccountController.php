<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateMyAccountRequest;

class UpdateMyAccountController extends Controller
{
    private UpdateMyAccountRequest $globalRequestObject;
    private array $sentInputs;
    private User $loggedInUser;

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

        $this->updateMyAccount();

        return response()->json([
            'message' => "User's account updated successfully!",
        ], 200);
    }
}
