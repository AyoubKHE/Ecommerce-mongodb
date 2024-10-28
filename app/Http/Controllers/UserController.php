<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\FirstAdminRegistrationRequest;
use App\Services\Users\clsEmailVerification;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

use App\Services\Users\clsFirstAdminRegistration;

class UserController extends Controller
{

    public function __construct(
        private clsFirstAdminRegistration $objFirstAdminRegistration,
        private clsEmailVerification $objEmailVerification
    ) {
    }


    public function firstAdminRegistration(FirstAdminRegistrationRequest $request): JsonResponse
    {
        return $this->objFirstAdminRegistration->main($request);
    }


    public function emailVerification(string $emailVerificationToken): JsonResponse
    {
        return $this->objEmailVerification->main($emailVerificationToken);
    }
}
