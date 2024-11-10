<?php

namespace App\Http\Controllers\Users;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetUserByIdController extends Controller
{
    private Request $globalRequestObject;
    private User|null $requestedUser;

    private function loadRequestedUser()
    {
        $this->requestedUser = User::where(
            "id",
            $this->globalRequestObject->userId
        )->first();

        if (!$this->requestedUser) {
            throw new Exception('Requested user not found.', 404);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->loadRequestedUser();

        return response()->json([
            'user' => $this->requestedUser,
        ], 200);
    }
}
