<?php

namespace Tests\Feature\Users;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\User;
use ReflectionClass;
use ReflectionMethod;
use Mockery\MockInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Requests\Users\UserCreationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Controllers\Users\SuperAdminRegistrationController;

class SuperAdminRegistrationTest extends TestCase
{
    use DatabaseMigrations;
    //! --filter=test_success_super_admin_registration
    public function test_success_super_admin_registration(): void
    {

        Storage::fake('public');

        $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

        // $this->partialMock('Illuminate\Http\UploadedFile', function (MockInterface $mock) {
        //     $mock->shouldReceive('store')
        //         ->withAnyArgs()
        //         ->andReturnNull();
        // });

        $mockImageFile = Mockery::mock(UploadedFile::class);

        // Définir ce que fait la méthode store
        $mockImageFile->shouldReceive('store')
                      ->withAnyArgs()  // accepte n'importe quels arguments
                      ->andReturnNull(); 

        $response = $this->postJson('api/users/super-admin-registration', [
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

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'Access denied. Only the super admin can register through this endpoint.'
        ]);

        // $response->assertStatus(201)
        //     ->assertJsonFragment(
        //         [
        //             'message' => 'User Account is created successfully. An email confirmation has been sent to the user. The confirmation link is valid for 15 minutes only.'
        //         ]
        //     );

        // $this->assertDatabaseHas(
        //     "users",
        //     ["username" => "aykhe"]
        // );

        // Storage::disk('public')->
        //     assertExists(
        //         $response->json()["user"]["profileImagePath"]
        //     );
    }

    // public function test_registration_denied_when_admin_already_exists()
    // {
    //     Storage::fake('public');

    //     $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

    //     $response = $this->postJson('api/users/super-admin-registration', [
    //         'firstName' => 'Ayoub',
    //         'lastName' => 'Kheyar',
    //         'username' => 'aykhe',
    //         'email' => 'ayoub.kheyar06@gmail.com',
    //         'password' => "1",
    //         'phone' => '0555555555',
    //         'address' => [
    //             'city' => 'Bejaia',
    //             'zip' => '06000',
    //             'streetNumber' => '0601',
    //             'addressLine' => 'Bejaia',
    //         ],
    //         'birthDate' => '2000-01-01',
    //         'profileImage' => $profileImage,
    //     ]);

    //     $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);

    //     $response = $this->postJson('api/users/super-admin-registration', [
    //         'firstName' => 'otherUser',
    //         'lastName' => 'otherUser',
    //         'username' => 'otherUser',
    //         'email' => 'a@a.com',
    //         'password' => "1",
    //         'phone' => '0555555556',
    //         'address' => [
    //             'city' => 'Bejaia',
    //             'zip' => '06000',
    //             'streetNumber' => '0601',
    //             'addressLine' => 'Bejaia',
    //         ],
    //         'birthDate' => '2000-01-01',
    //         'profileImage' => $profileImage,
    //     ]);

    //     $response->assertStatus(403)
    //         ->assertJsonFragment(
    //             ['error' => 'Access denied. Only the super admin can register through this endpoint.']
    //         );
    // }

    // public function test_store_user_handles_database_error()
    // {
    //     // Simuler une image de profil pour l'utilisateur
    //     Storage::fake('public');

    //     $profileImage = UploadedFile::fake()->image('profile_image.png')->size(4000);
    //     // Préparer les données pour la requête de création d'utilisateur
    //     $data = [
    //         'firstName' => 'Ayoub',
    //         'lastName' => 'Kheyar',
    //         'username' => 'aykhe',
    //         'email' => 'ayoub.kheyar06@gmail.com',
    //         'password' => "1",
    //         'phone' => '0555555555',
    //         'address' => [
    //             'city' => 'Bejaia',
    //             'zip' => '06000',
    //             'streetNumber' => '0601',
    //             'addressLine' => 'Bejaia',
    //         ],
    //         'birthDate' => '2000-01-01',
    //         'profileImage' => $profileImage,
    //         'role' => "Admin"
    //     ];

    //     // Créer une instance de UserCreationRequest avec les données
    //     $request = UserCreationRequest::create('/user/create', 'POST', $data);

    //     // Mock de User pour simuler une erreur lors de la création
    //     $this->mock(User::class, function (MockInterface $mock) {
    //         $mock->shouldReceive('create')
    //             ->andThrow(new Exception('An error occurred while accessing the database. Please try again later.'));
    //     });

    //     // Appeler l'endpoint et capturer la réponse
    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token, // Ajouter le jeton JWT dans les en-têtes
    //     ])->postJson('api/users/create', $data);

    //     // Vérification de la réponse
    //     $response->assertStatus(500);
    //     $response->assertJson([
    //         'message' => 'An error occurred while accessing the database. Please try again later.'
    //     ]);
    //}
}
