<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use App\Services\BackupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateMyAccountImageRequest;


class UpdateMyAccountImageController extends Controller
{
    private UpdateMyAccountImageRequest $globalRequestObject;
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

    private function updateProfileImagePathFieldInDatabase()
    {
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

    private function storeTheNewImageOnDisk()
    {
        if (
            !BackupService::createImagesBackup(
                "users",
                $this->loggedInUser->id
            )
        ) {
            throw new Exception(
                'An error occurred while creating a backup of the old image.',
                500
            );
        }

        if (
            !unlink(
                storage_path(
                    'app/public/' . $this->loggedInUser->profileImagePath
                )
            )
        ) {
            throw new Exception(
                'An error occurred while deleting the old image.',
                500
            );
        }

        $imageFile = $this->globalRequestObject->file('profileImage');
        $folderPath = 'users/id_' . $this->loggedInUser->id;

        $this->loggedInUser->profileImagePath = $imageFile->store($folderPath, 'public');
        if (!$this->loggedInUser->profileImagePath) {
            throw new Exception("An error occurred while saving the user's new profile image.", 500);
        }
    }

    public function __invoke(UpdateMyAccountImageRequest $request)
    {
        $this->globalRequestObject = $request;

        $this->loggedInUser = $this->globalRequestObject->get('loggedInUser');

        $this->storeTheNewImageOnDisk();

        try {
            $this->updateProfileImagePathFieldInDatabase();

            BackupService::deleteImagesBackup(
                "users",
                $this->loggedInUser->id
            );

            return response()->json([
                'message' => "User's account image updated successfully!",
            ], 200);

        } catch (Throwable $th) {

            BackupService::makeImagesRestoration(
                "users",
                $this->loggedInUser->id
            );
        }

    }
}
