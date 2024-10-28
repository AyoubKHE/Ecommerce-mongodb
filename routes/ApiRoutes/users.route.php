<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post(
    '/users/first-admin-register',
    [UserController::class, 'firstAdminRegistration']
);