<?php

namespace App\Jobs;

use App\Models\FormRequest;
use App\Services\DrivePort\DrivePortClient;
use App\Services\DrivePort\DrivePortException;
use App\Services\DrivePort\DrivePortPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadToDrivePort implements ShouldQueue, ShouldBeUnique
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

    public function handle(DrivePortClient $client, DrivePortPayloadBuilder $builder): void
    {
        $r = FormRequest::with('site')->findOrFail($this->formRequestId);
        $r->incrementCrmAttempts();

        $payload = $builder->build($r);

        try {
            $resp = $client->createContactForm($payload);
        } catch (DrivePortException $e) {
            if (! $e->retryable && $this->job !== null) {
                $this->fail($e);
                return;
            }
            throw $e;
        }

        $externalId = (string) (data_get($resp, 'id') ?? '');
        $r->markCrmSent($externalId !== '' ? $externalId : null);

        Log::info('Drive Port lead sent', [
            'form_request_id' => $r->id,
            'site_id'         => $r->site_id,
            'external_id'     => $externalId,
            'number'          => data_get($resp, 'number'),
            'payload'         => $payload,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        FormRequest::find($this->formRequestId)?->markCrmFailed($e->getMessage());

        Log::error('Drive Port lead failed', [
            'form_request_id' => $this->formRequestId,
            'error'           => $e->getMessage(),
        ]);
    }
}
