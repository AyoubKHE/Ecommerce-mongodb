<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\FirstAdminRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\Users\clsFirstAdminRegistration;

class UserController extends Controller
{

    public function __construct(
        private clsFirstAdminRegistration $clsFirstAdminRegistration,
    ) {
    }


    public function firstAdminRegistration(FirstAdminRegistrationRequest $request): JsonResponse
    {
        return $this->clsFirstAdminRegistration->main($request);
    }
}
