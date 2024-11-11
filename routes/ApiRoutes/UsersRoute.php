<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\SuperAdminRegistrationController;
use App\Http\Controllers\Users\UserLoginController;
use App\Http\Controllers\Users\UserLogoutController;
use App\Http\Controllers\Users\UserCreationController;
use App\Http\Controllers\Users\GetPaginatedUsersController;
use App\Http\Controllers\Users\GetUserByIdController;
use App\Http\Controllers\Users\GetMyAccountController;
use App\Http\Controllers\Users\RefreshUserAccessTokenController;
use App\Http\Controllers\Users\SuspendUserByIdController;
use App\Http\Controllers\Users\ActivateUserByIdController;
use App\Http\Controllers\Users\UpdateUserRoleByIdController;
use App\Http\Controllers\Users\DeleteUserByIdController;
use App\Http\Controllers\Users\UserPasswordResetController;
use App\Http\Controllers\Users\UserForgetPasswordController;
use App\Http\Controllers\Users\UserEmailVerificationController;
use App\Http\Controllers\Users\SendEmailVerificationLinkController;

Route::post(
    '/users/super-admin-registration',
    SuperAdminRegistrationController::class
)
    ->name('users.super-admin-registration');


Route::post(
    '/users/login',
    UserLoginController::class
)
    ->name('users.login');


Route::get(
    '/users/logout',
    UserLogoutController::class
)
    ->name('users.logout')
    ->middleware('JWTAuth');


Route::post(
    '/users/create',
    UserCreationController::class
)
    ->name('users.create')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::get(
    '/users/get-paginated-users',
    GetPaginatedUsersController::class
)
    ->name('users.get-paginated-users')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::get(
    '/users/get-by-id/{userId}',
    GetUserByIdController::class
)
    ->name('users.get-by-id')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::get(
    '/users/get-my-account',
    GetMyAccountController::class
)
    ->name('users.get-my-account')
    ->middleware('JWTAuth');


Route::get(
    '/users/refresh-token/{refreshToken}',
    RefreshUserAccessTokenController::class
)
    ->name('users.refresh-token')
    ->middleware('JWTAuth');


Route::put(
    '/users/suspend-by-id/{userId}',
    SuspendUserByIdController::class
)
    ->name('users.suspend-by-id')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::put(
    '/users/activate-by-id/{userId}',
    ActivateUserByIdController::class
)
    ->name('users.activate-by-id')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::put(
    '/users/update-role/{userId}',
    UpdateUserRoleByIdController::class
)
    ->name('users.update-role')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


Route::delete(
    '/users/delete-by-id/{userId}',
    DeleteUserByIdController::class
)
    ->name('users.delete-by-id')
    ->middleware('JWTAuth')
    ->middleware('SuperAdminAuthorization');


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