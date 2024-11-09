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

    public function __invoke(Request $request)
    {
        $this->globalRequestObject = $request;

        $this->preparePaginatedUsers();

        return response()->json([
            'users' => $this->formatPaginationResponse(),
        ], 200);
    }
}
