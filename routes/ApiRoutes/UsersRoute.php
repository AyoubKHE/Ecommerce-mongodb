<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\FirstAdminRegistrationController;
use App\Http\Controllers\Users\UserLoginController;
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