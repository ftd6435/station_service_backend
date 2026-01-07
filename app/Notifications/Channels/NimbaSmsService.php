<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Http;

class NimbaSmsService
{
    public function sendOtp(string $telephone, string $message)
    {
        try {
            $serviceId   = env('NIMBA_SMS_SERVICE_ID');
            $secretToken = env('NIMBA_SMS_SECRET_TOKEN');
            $basicToken  = env('NIMBA_SMS_BASIC_TOKEN');
            $sender      = env('NIMBA_SMS_SENDER');
            $url         = env('NIMBA_SMS_URL');

            if (strlen($sender) > 11) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le sender_name est trop long. Max 11 caractères.',
                ], 422);
            }

            if (! $serviceId || ! $secretToken || ! $sender || ! $url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres de configuration manquants pour le service Nimba.',
                ], 500);
            }

            $authHeader = "Basic " . $basicToken;

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept'        => 'application/json',
            ])->post($url, [
                'sender_name' => $sender,
                'to'          => [$telephone],
                'message'     => $message,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'SMS envoyé avec succès.',
                    'response' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS.',
                'error'   => [
                    'status_code' => $response->status(),
                    'detail'      => $response->json(),
                ],
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
