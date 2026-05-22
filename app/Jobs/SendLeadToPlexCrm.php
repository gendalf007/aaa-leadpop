<?php

namespace App\Jobs;

use App\Models\FormRequest;
use App\Services\PlexCrm\PlexCrmClient;
use App\Services\PlexCrm\PlexPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadToPlexCrm implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [10, 30, 60];

    public function __construct(public int $formRequestId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->formRequestId;
    }

    public function handle(PlexCrmClient $client, PlexPayloadBuilder $builder): void
    {
        $r = FormRequest::with('site')->findOrFail($this->formRequestId);
        $r->incrementCrmAttempts();

        $payload = $builder->build($r);
        $resp = $client->createContactForm($payload);

        $externalId = (string) (data_get($resp, 'id') ?? data_get($resp, 'data.id') ?? '');
        $r->markCrmSent($externalId !== '' ? $externalId : null);

        Log::info('Plex CRM lead sent', [
            'form_request_id' => $r->id,
            'site_id'         => $r->site_id,
            'external_id'     => $externalId,
            'payload'         => $payload,
            'response'        => $resp,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        FormRequest::find($this->formRequestId)?->markCrmFailed($e->getMessage());

        Log::error('Plex CRM lead failed', [
            'form_request_id' => $this->formRequestId,
            'error'           => $e->getMessage(),
        ]);
    }
}
