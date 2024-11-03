<?php

namespace Tests\Feature\Users;

use Tests\TestCase;
use App\Models\User;
use Mockery\MockInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FirstAdminRegistrationTest extends TestCase
{
    use DatabaseMigrations;
    //! --filter=test_success_first_admin_registration
    public function test_success_first_admin_registration(): void
    {

        Storage::fake('public');

        $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

        $response = $this->postJson('api/users/first-admin-registration', [
            'firstName' => 'Ayoub',
            'lastName' => 'Kheyar',
            'username' => 'aykhe',
            'email' => 'ayoub.kheyar06@gmail.com',
            'password' => "1",
            'phone' => '0555555555',
            'address' => [
                'city' => 'Bejaia',
                'zip' => '06000',
                'streetNumber' => '0601',
                'addressLine' => 'Bejaia',
            ],
            'birthDate' => '2000-01-01',
            'profileImage' => $profileImage,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(
                [
                    'message' => 'User Account is created successfully. An email confirmation has been sent to the user. The confirmation link is valid for 15 minutes only.'
                ]
            );

        $this->assertDatabaseHas(
            "users",
            ["username" => "aykhe"]
        );

        Storage::disk('public')->
            assertExists(
                $response->json()["user"]["profileImagePath"]
            );
    }

    public function test_registration_denied_when_admin_already_exists()
    {
        Storage::fake('public');

        $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

        $response = $this->postJson('api/users/first-admin-registration', [
            'firstName' => 'Ayoub',
            'lastName' => 'Kheyar',
            'username' => 'aykhe',
            'email' => 'ayoub.kheyar06@gmail.com',
            'password' => "1",
            'phone' => '0555555555',
            'address' => [
                'city' => 'Bejaia',
                'zip' => '06000',
                'streetNumber' => '0601',
                'addressLine' => 'Bejaia',
            ],
            'birthDate' => '2000-01-01',
            'profileImage' => $profileImage,
        ]);

        $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

        $response = $this->postJson('api/users/first-admin-registration', [
            'firstName' => 'otherUser',
            'lastName' => 'otherUser',
            'username' => 'otherUser',
            'email' => 'a@a.com',
            'password' => "1",
            'phone' => '0555555556',
            'address' => [
                'city' => 'Bejaia',
                'zip' => '06000',
                'streetNumber' => '0601',
                'addressLine' => 'Bejaia',
            ],
            'birthDate' => '2000-01-01',
            'profileImage' => $profileImage,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(
                ['error' => 'Access denied. Only the first admin can register through this endpoint.']
            );
    }
}
