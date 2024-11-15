<?php

namespace Database\Seeders;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use Illuminate\Database\Seeder;
use App\Models\SystemPermission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    private ObjectId $_id;

    private User|null $superAdmin;

    private function buildUserPermissions()
    {
        $userPermissions = [];
        try {
            $systemPermissions = SystemPermission::all();
        } catch (\Throwable $th) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

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

        return $filePath;
    }

    private function storeUser()
    {
        $this->_id = new ObjectId();

        $profileImagePath = $this->storeProfileImage();

        try {

            $role = fake()->randomElement(['Admin', 'User']);

            if ($role === "User") {
                $userPermissions = $this->buildUserPermissions();
            } else {
                $userPermissions = null;
            }

            $createdAt = fake()->dateTimeBetween('-3 years', 'now');

            $updatedAt = fake()->dateTimeBetween($createdAt, 'now');

            $deletedAt = fake()->boolean(30) ? fake()->dateTimeBetween($updatedAt, 'now') : null;
            if ($deletedAt) {
                $deletedBy = [
                    "id" => new ObjectId($this->superAdmin->id),
                    "firstName" => $this->superAdmin->firstName,
                    "lastName" => $this->superAdmin->lastName,
                    "username" => $this->superAdmin->username,
                    "email" => $this->superAdmin->email,
                ];
            } else {
                $deletedBy = null;
            }

            $lastLogin = fake()->dateTimeBetween($createdAt, $deletedAt === null ? now() : $deletedAt);

            $user = new User([
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
                'createdBy' => [
                    "id" => new ObjectId($this->superAdmin->id),
                    "firstName" => $this->superAdmin->firstName,
                    "lastName" => $this->superAdmin->lastName,
                    "username" => $this->superAdmin->username,
                    "email" => $this->superAdmin->email,
                ],
                'deletedBy' => $deletedBy,
            ]);

            if (fake()->boolean(60)) {
                $user->updatedBy = [
                    "id" => new ObjectId($user->id),
                    "firstName" => $user->firstName,
                    "lastName" => $user->lastName,
                    "username" => $user->username,
                    "email" => $user->email,
                ];
            } else {
                $user->updatedBy = [
                    "id" => new ObjectId($this->superAdmin->id),
                    "firstName" => $this->superAdmin->firstName,
                    "lastName" => $this->superAdmin->lastName,
                    "username" => $this->superAdmin->username,
                    "email" => $this->superAdmin->email,
                ];
            }

            $isCreated = $user->save();

            if (!$isCreated) {
                throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
            }

        } catch (Throwable $th) {
            Storage::deleteDirectory("public/users/id_" . $this->_id->__toString());
        }

    }

    private function storeSuperAdmin()
    {
        $this->_id = new ObjectId();

        $profileImagePath = $this->storeProfileImage();

        try {

            $this->superAdmin = User::create([
                '_id' => $this->_id,
                'firstName' => 'Ayoub',
                'lastName' => 'Kheyar',
                'username' => 'a',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make("a"),
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
                'role' => 'Super Admin',
                'permissions' => null,
                'lastLogin' => now(),
                'createdAt' => Carbon::create(2020, 1, 1, 0, 0, 0),
                'updatedAt' => Carbon::create(2020, 1, 1, 0, 0, 0),
                'deletedAt' => null,
                'createdBy' => null,
                'updatedBy' => null,
                'deletedBy' => null,
            ]);

            if (!$this->superAdmin) {
                throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
            }
        } catch (Throwable $th) {

            Storage::deleteDirectory("public/users/id_" . $this->_id->__toString());

            throw $th;
        }

    }


    public function run(): void
    {

        $count = 10;

        try {
            $usersCount = User::count();
        } catch (Throwable $th) {
            return;
        }

        if ($usersCount === 0) {
            try {
                $this->storeSuperAdmin();
                $count -= 1;
            } catch (Throwable $th) {
                return;
            }
        }

        for ($i = 1; $i <= $count; $i++) {
            $this->storeUser();
        }
    }
}
