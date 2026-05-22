<?php

namespace App\Services\PlexCrm;

use App\Services\PlexCrm\Exceptions\PlexCrmException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class PlexCrmClient
{
    public function createContactForm(array $payload): array
    {
        $cfg = config('services.plex_crm');

        $response = Http::baseUrl($cfg['base_url'])
            ->withToken($cfg['token'])
            ->timeout($cfg['timeout'])
            ->retry(
                $cfg['retry_times'],
                $cfg['retry_sleep_ms'],
                function (\Throwable $e) {
                    if ($e instanceof ConnectionException) {
                        return true;
                    }
                    if ($e instanceof RequestException) {
                        return $e->response?->serverError() === true;
                    }
                    return false;
                },
                throw: false
            )
            ->acceptJson()
            ->asJson()
            ->post('/contact/form', $payload);

        if (! $response->successful()) {
            throw new PlexCrmException(sprintf(
                'Plex CRM returned HTTP %d: %s',
                $response->status(),
                $response->body()
            ));
        }

        return (array) $response->json();
    }
}
