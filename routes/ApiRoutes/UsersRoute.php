<?php

use App\Http\Controllers\Users\FirstAdminRegistrationController;
use App\Http\Controllers\Users\SendEmailVerificationLinkController;
use App\Http\Controllers\Users\UserEmailVerificationController;
use App\Http\Controllers\Users\UserForgetPasswordController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/users/first-admin-registration',
    FirstAdminRegistrationController::class
)
    ->name('users.first-admin-registration');


Route::get(
    '/users/email-verification/{emailVerificationToken}',
    UserEmailVerificationController::class
)
    ->name('users.email-verification');

Route::post(
    '/users/send-email-verification-link',
    SendEmailVerificationLinkController::class
)
    ->name('users.send-email-verification-link');

Route::post(
    '/users/forget-password',
    UserForgetPasswordController::class
)
    ->name('users.forget-password');