<?php

namespace App\Http\Controllers;

use App\Services\Users\clsSendEmailVerificationLink;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Users\clsEmailVerification;
use App\Services\Users\clsFirstAdminRegistration;

use App\Http\Requests\Users\FirstAdminRegistrationRequest;
use App\Http\Requests\Users\sendEmailVerificationLinkRequest;

class UserController extends Controller
{

    public function __construct(
        private clsFirstAdminRegistration $objFirstAdminRegistration,
        private clsEmailVerification $objEmailVerification,
        private clsSendEmailVerificationLink $objSendEmailVerificationLink
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


    public function sendEmailVerificationLink(sendEmailVerificationLinkRequest $request)
    {
        return $this->objSendEmailVerificationLink->main($request);
    }
}
