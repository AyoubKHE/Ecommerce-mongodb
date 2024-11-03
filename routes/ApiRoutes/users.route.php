<?php

use App\Http\Controllers\Users\FirstAdminRegistrationController;
use App\Http\Controllers\Users\SendEmailVerificationLinkController;
use App\Http\Controllers\Users\UserEmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/users/first-admin-registration',
    FirstAdminRegistrationController::class
)
    ->name('users.first-admin-registration');


Route::get(
    '/users/user-email-verification/{emailVerificationToken}',
    UserEmailVerificationController::class
)
    ->name('users.user-email-verification');

Route::post(
    '/users/send-email-verification-link',
    SendEmailVerificationLinkController::class
)
    ->name('users.send-email-verification-link');