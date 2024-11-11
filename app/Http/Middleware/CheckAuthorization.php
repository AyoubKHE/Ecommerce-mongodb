<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorization
{
    private $resourcesNamesInDatabaseMapping = [
        "products" => "Produits",
        "productsCategories" => "Catégories Des Produits",
    ];

    private $operationsValues = [
        "GET" => 1,
        "POST" => 2,
        "PUT" => 4,
        "DELETE" => 8,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requestedRessource): Response
    {

        $loggedInUser = $request->get('loggedInUser');

        if ($loggedInUser->role === "Admin") {
            return $next($request);
        }

        $requestedRessourceNameInDatabase = $this->resourcesNamesInDatabaseMapping[$requestedRessource];

        $requestedRessourceOperation = $request->getMethod();

        foreach ($loggedInUser->permissions as $permission) {
            if ($permission['name'] === $requestedRessourceNameInDatabase) {

                $requestedRessourceOperationValue = $this->operationsValues[$requestedRessourceOperation];

                if (
                    (int) $permission['value'] === -1
                    || ((int) $permission['value'] & $requestedRessourceOperationValue) === $requestedRessourceOperationValue
                ) {
                    return $next($request);
                } else {
                    throw new Exception(
                        "Access denied: User do not have the necessary permissions to perform this action.",
                        403
                    );
                }
            }
        }

        throw new Exception("Internal Server Error.", 500);
    }
}
