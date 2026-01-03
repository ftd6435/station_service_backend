<?php

namespace App\Modules\Caisse\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Caisse\Models\TypeOperation;
use App\Modules\Caisse\Resources\OperationCompteResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class OperationCompteService
{
    public function getAll()
    {
        try {

            $operations = OperationCompte::visible()
                ->with([
                    'typeOperation',
                    'compte.station',
                    'source.station',
                    'destination.station',
                    'createdBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => OperationCompteResource::collection($operations),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations.',
            ], 500);
        }
    }

    public function getOne(int $id)
    {
        try {

            $operation = OperationCompte::visible()
                ->with([
                    'typeOperation',
                    'compte.station',
                    'source.station',
                    'destination.station',
                    'createdBy',
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new OperationCompteResource($operation),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Opération introuvable.',
            ], 404);
        }
    }

    /**
     * OPÉRATION SIMPLE (ENTRÉE / SORTIE)
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $compte = Compte::lockForUpdate()->find($data['id_compte']);
            $type   = TypeOperation::find($data['id_type_operation']);

            if (! $compte || ! $type) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte ou type d’opération introuvable.',
                ], 404);
            }

            if ($type->nature === 2) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Utilisez le transfert inter-station. en precisant la source et la destination',
                ], 409);
            }

            if ($type->nature === 0 && $data['montant'] > $compte->solde_actuel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant.',
                ], 409);
            }

            $operation = OperationCompte::create([
                'id_compte'         => $compte->id,
                'id_type_operation' => $type->id,
                'montant'           => $data['montant'],
                'reference'         => $data['reference'] ?? null,
                'commentaire'       => $data['commentaire'] ?? null,
                'status'            => 'effectif',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Opération enregistrée.',
                'data'    => new OperationCompteResource(
                    $operation->load([
                        'typeOperation',
                        'compte.station',
                        'createdBy',
                    ])
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’opération.',
            ], 500);
        }
    }

    /**
     * TRANSFERT INTER-STATION
     */
    public function transfer(array $data)
    {
        DB::beginTransaction();

        try {

            $source = Compte::lockForUpdate()->find($data['id_compte_source']);
            $dest   = Compte::lockForUpdate()->find($data['id_compte_destination']);

            if (! $source || ! $dest) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Compte source ou destination introuvable.',
                ], 404);
            }

            if ($data['montant'] > $source->solde_actuel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Solde insuffisant.',
                ], 409);
            }

            $type = TypeOperation::where('nature', 2)->first();

            if (! $type) {
                return response()->json([
                    'status'  => 500,
                    'message' => 'Type transfert non configuré.',
                ], 500);
            }

            $opSource = OperationCompte::create([
                'id_compte'         => $source->id,
                'id_source'         => $source->id,
                'id_destination'    => $dest->id,
                'id_type_operation' => $type->id,
                'montant'           => $data['montant'],
                'reference'         => $data['reference'] ?? null,
                'commentaire'       => $data['commentaire'] ?? null,
                'status'            => 'en_attente',
            ]);

            OperationCompte::create([
                'id_compte'         => $dest->id,
                'id_source'         => $source->id,
                'id_destination'    => $dest->id,
                'id_type_operation' => $type->id,
                'montant'           => $data['montant'],
                'reference'         => $opSource->reference,
                'commentaire'       => $data['commentaire'] ?? null,
                'status'            => 'en_attente',
            ]);

            DB::commit();

            return response()->json([
                'status'    => 201,
                'message'   => 'Transfert envoyé.',
                'reference' => $opSource->reference,
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du transfert.',
            ], 500);
        }
    }

    public function confirm(string $reference)
    {
        DB::beginTransaction();

        try {

            $operations = OperationCompte::where('reference', $reference)
                ->lockForUpdate()
                ->get();

            if ($operations->count() !== 2) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Transfert introuvable.',
                ], 404);
            }

            foreach ($operations as $op) {
                $op->update(['status' => 'effectif']);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Transfert confirmé.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la confirmation.',
            ], 500);
        }
    }

    public function cancel(string $reference)
    {
        DB::beginTransaction();

        try {

            $operations = OperationCompte::where('reference', $reference)
                ->lockForUpdate()
                ->get();

            if ($operations->isEmpty()) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Transfert introuvable.',
                ], 404);
            }

            foreach ($operations as $op) {
                $op->update(['status' => 'annule']);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Transfert annulé.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’annulation.',
            ], 500);
        }
    }
}
