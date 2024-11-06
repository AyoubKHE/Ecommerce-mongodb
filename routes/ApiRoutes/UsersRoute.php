<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\FirstAdminRegistrationController;
use App\Http\Controllers\Users\UserLoginController;
use App\Http\Controllers\Users\UserCreationController;
use App\Http\Controllers\Users\GetPaginatedUsersController;
use App\Http\Controllers\Users\GetUserByIdController;
use App\Http\Controllers\Users\SendEmailVerificationLinkController;
use App\Http\Controllers\Users\UserEmailVerificationController;
use App\Http\Controllers\Users\UserForgetPasswordController;
use App\Http\Controllers\Users\UserPasswordResetController;

Route::post(
    '/users/first-admin-registration',
    FirstAdminRegistrationController::class
)
    ->name('users.first-admin-registration');


Route::post(
    '/users/login',
    UserLoginController::class
)
    ->name('users.login');


Route::post(
    '/users/create',
    UserCreationController::class
)
    ->name('users.create')
    ->middleware('JWTAuth');


Route::get(
    '/users/get-paginated-users',
    GetPaginatedUsersController::class
)
    ->name('users.get-paginated-users')
    ->middleware('JWTAuth');


Route::get(
    '/users/get-by-id/{userId}',
    GetUserByIdController::class
)
    ->name('users.email-verification')
    ->middleware('JWTAuth');


Route::post(
    '/users/send-email-verification-link',
    SendEmailVerificationLinkController::class
)
    ->name('users.send-email-verification-link');


Route::get(
    '/users/email-verification/{emailVerificationToken}',
    UserEmailVerificationController::class
)
    ->name('users.email-verification');


Route::post(
    '/users/forget-password',
    UserForgetPasswordController::class
)
    ->name('users.forget-password');


Route::post(
    '/users/reset-password',
    UserPasswordResetController::class
)
    ->name('users.reset-password');