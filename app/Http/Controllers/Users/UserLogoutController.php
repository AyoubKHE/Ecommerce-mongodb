<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserLogoutController extends Controller
{
    private Request $globalRequestObject;

    private function logoutUser()
    {
        $loggedInUser = $this->globalRequestObject->get('loggedInUser');

        if (!$loggedInUser->refreshToken) {
            throw new Exception('The User Already logged out.', 403);
        }

        $loggedInUser->refreshToken = null;

        try {
            $isUpdated = $loggedInUser->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$isUpdated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->logoutUser();

        return response()->json([
            'message' => 'User logged out successfully!',
        ], status: 200);
    }
}
