<?php

namespace App\Services\DrivePort;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DrivePortClient
{
    public function createContactForm(array $payload): array
    {
        $cfg = config('services.drive_port');

        try {
            // Секрет — заголовком, а не ?secret=, чтобы не светить его в логах URL.
            $response = Http::withHeaders(['X-Contact-Form-Secret' => $cfg['secret']])
                ->timeout($cfg['timeout'])
                ->acceptJson()
                ->asJson()
                ->post($cfg['url'], $payload);
        } catch (ConnectionException $e) {
            throw new DrivePortException('Drive Port connection error: ' . $e->getMessage());
        }

        if ($response->status() !== 201) {
            throw new DrivePortException(
                sprintf('Drive Port returned HTTP %d: %s', $response->status(), $response->body()),
                retryable: ! $response->clientError(),
            );
        }

        return (array) $response->json();
    }
}
