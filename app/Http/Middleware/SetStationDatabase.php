<?php

namespace App\Http\Middleware;

use App\Modules\Backoffice\Models\Client;
use App\Modules\Backoffice\Models\Licence;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetStationDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * =====================================================
         * 🔹 1. Code station / client (HEADER)
         * =====================================================
         */
        $stationCode = $request->header('code');

        if (! $stationCode) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Code station requis dans l’en-tête "code".',
            ], 400);
        }

        /**
         * =====================================================
         * 🔹 2. Client (BASE MASTER)
         * =====================================================
         */
        $client = Client::where('code', $stationCode)->first();

        if (! $client) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Station / client introuvable.',
            ], 404);
        }

        if (! $client->is_created) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La base de données de cette station n’a pas encore été créée.',
            ], 500);
        }

        if (! $client->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cette station est suspendue. Veuillez contacter l’administration.',
            ], 403);
        }

        /**
         * =====================================================
         * 🔹 3. Licence (via client_id)
         * =====================================================
         */
        $licence = Licence::where('client_id', $client->id)->first();

        if (! $licence) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aucune licence active trouvée pour cette station.',
            ], 403);
        }

        /**
         * =====================================================
         * 🔹 4. Vérification dates licence
         * =====================================================
         */
        $now = Carbon::now();

        if ($now->lt($licence->date_achat)) {
            $request->merge(['_licence_jours_restants' => null]);

        } elseif ($now->gt($licence->date_expiration)) {

            $joursExp = $now->diffInDays($licence->date_expiration);
            $request->merge(['_licence_jours_restants' => -$joursExp]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Licence expirée.',
                'jours_depassement' => $joursExp,
            ], 403);

        } else {
            $joursRestants = $now->diffInDays($licence->date_expiration);
            $request->merge(['_licence_jours_restants' => $joursRestants]);
        }

        /**
         * =====================================================
         * 🔹 5. Switch DB CLIENT (TENANT)
         * =====================================================
         */
        try {

            $request->merge(['db_name' => $client->database]);

            // On garde mysql par défaut, comme dans ton autre projet
            Config::set('database.connections.mysql.database', $client->database);

            DB::purge('mysql');
            DB::reconnect('mysql');

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Impossible de se connecter à la base de données de la station.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        /**
         * =====================================================
         * 🔹 6. Partage du client courant
         * =====================================================
         */
        $request->attributes->set('client', $client);

        return $next($request);
    }
}
