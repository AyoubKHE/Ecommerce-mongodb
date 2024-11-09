<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetMyAccountController extends Controller
{

    public function __invoke(Request $request)
    {
        $loggedInUser = $request->get('loggedInUser');

        return response()->json([
            'user' => $loggedInUser,
        ], 200);

    }
}
