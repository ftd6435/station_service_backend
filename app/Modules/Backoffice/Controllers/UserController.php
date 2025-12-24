<?php

namespace App\Modules\Backoffice\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Models\Client;
use App\Modules\Backoffice\Models\Company;
use App\Modules\Backoffice\Models\User;
use App\Modules\Backoffice\Resources\ClientResource;
use App\Modules\Backoffice\Resources\CompanyResource;
use App\Traits\ApiResponses;
use App\Traits\GenerateLicence;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponses, GenerateLicence;

    /**
     * Récupère et affiche tous les utilisateurs
     */
    public function index()
    {
        return $this->successResponse(User::all(), "La liste de tous les utilisateurs");
    }

    /**
     * Récupérer toutes les entreprises et les afficher
     */
    public function clients()
    {
        $clients = Client::with('licences')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(ClientResource::collection($clients), "La liste de tous les clients bien chargé.");
    }

    /**
     * Modification du role d'un utilisateur
     */
    public function role(Request $request, $id)
    {
        try {
            $request->validate([
                'role' => "required|string|in:super_admin,admin,user"
            ]);

            $user = User::where('id', $id)->first();

            if (! $user) {
                return $this->errorResponse("Cet utilisateur n'existe pas", 401);
            }

            $user->update([
                'role' => $request->role
            ]);

            return $this->successResponse($user, "Le role a été modifié avec succès");
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
