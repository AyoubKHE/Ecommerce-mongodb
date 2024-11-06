<?php

namespace App\Http\Controllers\Users;

use Illuminate\Database\Eloquent\Collection;
use Throwable;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPaginatedUsersController extends Controller
{
    private Request $globalRequestObject;
    private User|null $loggedInUser;
    private LengthAwarePaginator $paginatedUsers;
    // private Collection $paginatedUsers;

    function formatPaginationResponse()
    {
        return [
            'currentPage' => $this->paginatedUsers->currentPage(),
            'data' => $this->paginatedUsers->items(),
            'firstPageUrl' => $this->paginatedUsers->url(1),
            'from' => $this->paginatedUsers->firstItem(),
            'lastPage' => $this->paginatedUsers->lastPage(),
            'lastPageUrl' => $this->paginatedUsers->url($this->paginatedUsers->lastPage()),
            'links' => $this->paginatedUsers->linkCollection()->toArray(),
            'nextPageUrl' => $this->paginatedUsers->nextPageUrl(),
            'path' => $this->paginatedUsers->path(),
            'perPage' => $this->paginatedUsers->perPage(),
            'prevPageUrl' => $this->paginatedUsers->previousPageUrl(),
            'to' => $this->paginatedUsers->lastItem(),
            'total' => $this->paginatedUsers->total(),
        ];
    }

    private function preparePaginatedUsers()
    {
        $page = $this->globalRequestObject->get('page', 1);
        $limit = $this->globalRequestObject->get('limit', 10);

        $this->paginatedUsers = User::paginate(perPage: $limit, page: $page);
        // $this->paginatedUsers = User::all();
    }

    private function checkLoggedInUserPermissions()
    {
        if ($this->loggedInUser->role === "Admin") {
            return;
        }

        foreach ($this->loggedInUser->permissions as $permission) {
            if ($permission['name'] === 'Utilisateurs') {
                if ((int) $permission['value'] === -1 || ((int) $permission['value'] & 1) === 1) {
                    return;
                } else {
                    throw new Exception(
                        "Access denied: You do not have the necessary permissions to perform this action.",
                        403
                    );
                }
            }
        }
    }

    private function loadLoggedInUserFromDatabase()
    {
        try {
            $this->loggedInUser = User::where(
                "id",
                $this->globalRequestObject->get('userId')
            )->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->loggedInUser) {
            throw new Exception('Logged in user not found.', 404);
        }
    }

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->loadLoggedInUserFromDatabase();

        $this->checkLoggedInUserPermissions();

        $this->preparePaginatedUsers();

        return response()->json([
            'users' => $this->formatPaginationResponse(),
        ], 200);
    }
}
