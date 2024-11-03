<?php

namespace Database\Seeders;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Faker\Core\File;

use MongoDB\BSON\ObjectId;
use Illuminate\Database\Seeder;
use App\Models\SystemPermission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    private ObjectId $_id;

    private bool $isUserImageFolderCreated = false;


    private function getRandomCreatedBy()
    {
        $users = User::all();

        $filteredUsers = $users->filter(function ($user) {

            if ($user->role === 'Admin') {
                return true;
            } else {
                foreach ($user->permissions as $permission) {
                    if (
                        $permission['name'] === 'Utilisateurs' &&
                        ((int) $permission['value'] === -1 || ((int) $permission['value'] & 2) === 2)
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        $randomUser = $filteredUsers->random();

        return [
            "id" => new ObjectId($randomUser->id),
            "firstName" => $randomUser->firstName,
            "lastName" => $randomUser->lastName,
            "username" => $randomUser->username,
            "email" => $randomUser->email,
            "role" => $randomUser->role,
            "permissions" => $randomUser->role === "Admin" ? null : $randomUser->permissions
        ];
    }

    private function getRandomUpdatedBy()
    {
        $users = User::all();

        $filteredUsers = $users->filter(function ($user) {

            if ($user->role === 'Admin') {
                return true;
            } else {
                foreach ($user->permissions as $permission) {
                    if (
                        $permission['name'] === 'Utilisateurs' &&
                        ((int) $permission['value'] === -1 || ((int) $permission['value'] & 4) === 4)
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        $randomUser = $filteredUsers->random();

        return [
            "id" => new ObjectId($randomUser->id),
            "firstName" => $randomUser->firstName,
            "lastName" => $randomUser->lastName,
            "username" => $randomUser->username,
            "email" => $randomUser->email,
            "role" => $randomUser->role,
            "permissions" => $randomUser->role === "Admin" ? null : $randomUser->permissions
        ];
    }

    private function getRandomDeletedBy()
    {
        $users = User::all();

        $filteredUsers = $users->filter(function ($user) {

            if ($user->role === 'Admin') {
                return true;
            } else {
                foreach ($user->permissions as $permission) {
                    if (
                        $permission['name'] === 'Utilisateurs' &&
                        ((int) $permission['value'] === -1 || ((int) $permission['value'] & 8) === 8)
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        $randomUser = $filteredUsers->random();

        return [
            "id" => new ObjectId($randomUser->id),
            "firstName" => $randomUser->firstName,
            "lastName" => $randomUser->lastName,
            "username" => $randomUser->username,
            "email" => $randomUser->email,
            "role" => $randomUser->role,
            "permissions" => $randomUser->role === "Admin" ? null : $randomUser->permissions
        ];

    }

    private function buildUserPermissions()
    {
        $userPermissions = [];
        $systemPermissions = SystemPermission::all();
        foreach ($systemPermissions as $systemPermission) {
            array_push($userPermissions, [
                "name" => $systemPermission->name,
                "value" => fake()->numberBetween(-1, 15)
            ]);
        }

        return $userPermissions;
    }

    private function storeProfileImage(): string
    {
        $folderPath = "users/id_" . $this->_id->__toString();

        $image = UploadedFile::fake()->image('profile.png');

        $filePath = $image->store($folderPath, 'public');
        if (!$filePath) {
            throw new Exception("An error occurred while saving the user's profile image.", 500);
        }

        $this->isUserImageFolderCreated = true;

        return $filePath;
    }

    private function storeUser()
    {

        try {
            $this->_id = new ObjectId();

            $profileImagePath = $this->storeProfileImage();

            $role = fake()->randomElement(['Admin', 'User']);

            if ($role === "User") {
                $userPermissions = $this->buildUserPermissions();
            } else {
                $userPermissions = null;
            }

            $createdAt = fake()->dateTimeBetween('-3 years', 'now');
            $createdBy = $this->getRandomCreatedBy();

            $updatedAt = fake()->dateTimeBetween($createdAt, 'now');
            $updatedBy = $this->getRandomUpdatedBy();

            $deletedAt = fake()->boolean(30) ? fake()->dateTimeBetween($updatedAt, 'now') : null;
            if ($deletedAt) {
                $deletedBy = $this->getRandomDeletedBy();
            } else {
                $deletedBy = null;
            }
            $lastLogin = fake()->dateTimeBetween($createdAt, $deletedAt === null ? now() : $deletedAt);

            User::create([
                '_id' => $this->_id,
                'firstName' => fake()->firstName(),
                'lastName' => fake()->lastName(),
                'username' => fake()->unique()->userName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make("111111"),
                'phone' => fake()->unique()->phoneNumber(),
                'address' => [
                    'city' => fake()->city(),
                    'zip' => fake()->postcode(),
                    'streetNumber' => fake()->streetAddress(),
                    'addressLine' => fake()->address(),
                ],
                'birthDate' => fake()->date(max: '-25 years'),
                'profileImagePath' => $profileImagePath,
                'isActive' => fake()->boolean(),
                'emailVerificationToken' => null,
                'passwordResetToken' => null,
                'refreshToken' => null,
                'role' => $role,
                'permissions' => $userPermissions,
                'lastLogin' => $lastLogin,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'deletedAt' => $deletedAt,
                'createdBy' => $createdBy,
                'updatedBy' => $updatedBy,
                'deletedBy' => $deletedBy,
            ]);
        } catch (\Throwable $th) {
            if ($this->isUserImageFolderCreated) {
                Storage::deleteDirectory("public/users/id_" . $this->_id->__toString());
            }
        }

    }

    private function storeFirstAdmin()
    {

        try {

            $this->_id = new ObjectId();

            $profileImagePath = $this->storeProfileImage();

            User::create([
                '_id' => $this->_id,
                'firstName' => 'Ayoub',
                'lastName' => 'Kheyar',
                'username' => 'a',
                'email' => 'a@a.com',
                'password' => Hash::make("1"),
                'phone' => '05 55 55 55 55',
                'address' => [
                    'city' => 'Bejaia',
                    'zip' => '06000',
                    'streetNumber' => '0601',
                    'addressLine' => 'Bejaia',
                ],
                'birthDate' => '2000-01-01',
                'profileImagePath' => $profileImagePath,
                'isActive' => true,
                'emailVerificationToken' => null,
                'passwordResetToken' => null,
                'refreshToken' => null,
                'role' => 'Admin',
                'permissions' => null,
                'lastLogin' => now(),
                'createdAt' => Carbon::create(2020, 1, 1, 0, 0, 0),
                'updatedAt' => Carbon::create(2020, 1, 1, 0, 0, 0),
                'deletedAt' => null,
                'createdBy' => null,
                'updatedBy' => null,
                'deletedBy' => null,
            ]);
        } catch (\Throwable $th) {
            if ($this->isUserImageFolderCreated) {
                Storage::deleteDirectory("public/users/id_" . $this->_id->__toString());
            }
            throw $th;
        }

    }


    public function run(): void
    {

        $count = 20;

        if (User::count() === 0) {
            try {
                $this->storeFirstAdmin();
                $count -= 1;
            } catch (\Throwable $th) {
                return;
            }
        }

        for ($i = 1; $i <= $count; $i++) {
            $this->isUserImageFolderCreated = false;
            $this->storeUser();
        }
    }
}
