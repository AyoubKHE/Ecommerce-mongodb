<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post(
    '/users/first-admin-register',
    [UserController::class, 'firstAdminRegistration']
);

Route::get(
    '/users/email-verification/{emailVerificationToken}',
    [UserController::class, 'emailVerification']
)
    ->name('users.email-verification');

Route::post(
    '/users/send-email-verification-link',
    [UserController::class, 'sendEmailVerificationLink']
)
    ->name('users.send-email-verification-link');