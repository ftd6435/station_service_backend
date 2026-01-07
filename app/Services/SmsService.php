<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class SmsService
{
    protected string $serviceId;
    protected string $secretToken;
    protected string $basicToken;
    protected string $sender;
    protected string $url;

    public function __construct()
    {
        $this->serviceId   = config('services.nimba.service_id');
        $this->secretToken = config('services.nimba.secret');
        $this->basicToken  = config('services.nimba.basic_token');
        $this->sender      = config('services.nimba.sender');
        $this->url         = config('services.nimba.url');

        if (! $this->serviceId || ! $this->secretToken || ! $this->basicToken || ! $this->sender || ! $this->url) {
            throw new Exception('Nimba SMS configuration is incomplete.');
        }

        if (strlen($this->sender) > 11) {
            throw new Exception('Sender name must not exceed 11 characters.');
        }
    }

    public function sendMessage(string $phone, string $message): array
    {
        /** @var Response $response */
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->basicToken,
            'Accept'        => 'application/json',
        ])->post($this->url, [
            'sender_name' => $this->sender,
            'to' => [$phone],
            'message' => $message,
        ]);

        if (! $response->successful()) {
            throw new Exception(
                "Failed to send SMS (HTTP {$response->status()}): " .
                    json_encode($response->json())
            );
        }

        return $response->json();
    }
}
