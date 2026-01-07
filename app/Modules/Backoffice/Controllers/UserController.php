<?php

namespace App\Modules\Backoffice\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Models\Client;
use App\Modules\Backoffice\Models\Company;
use App\Modules\Backoffice\Models\User;
use App\Modules\Backoffice\Requests\ClientNotificationRequest;
use App\Modules\Backoffice\Resources\ClientResource;
use App\Modules\Backoffice\Resources\CompanyResource;
use App\Services\SmsService;
use App\Traits\ApiResponses;
use App\Traits\GenerateLicence;
use App\Traits\RunMigrations;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponses, GenerateLicence, RunMigrations;

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

    /** Run new migrations to tenants' database */
    public function updateMigrations()
    {
        $clients = Client::where('is_created', true)->where('is_active', true)->get();

        if (!$clients) {
            return $this->errorResponse("Il n'y a aucune base de données active a mettre a jour.");
        }

        foreach ($clients as $client) {
            if ($this->runPendingMigrations('mysql', 'database/migrations/tenant', $client->database)) {
                Log::info('Migrations', ['migration' => "Migration de nouvelle tables pour $client->code : $client->database"]);
            }
        }

        return $this->successResponse([], 'Base de données des clients active mise a jour avec succès.');
    }

    /**
     * Send messages to all the clients.
     */
    public function notifyClients(ClientNotificationRequest $request, SmsService $smsService)
    {
        $fields = $request->validated();

        if (!empty($fields['data'])) {
            $telephones = Client::whereIn('id', $fields['data'])
                ->pluck('telephone')
                ->toArray();
        } else {
            $telephones = Client::pluck('telephone')->toArray();
        }

        $titre = $fields['titre'];
        $contenu = $fields['message'];

        $message = "$titre\n\n$contenu";

        $smsService->sendMessageToMany($telephones, $message);

        return $this->successResponse($fields, 'Message envoyé aux clients avec succès.');
    }
}
