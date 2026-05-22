<?php

namespace App\Jobs;

use App\Models\FormRequest;
use App\Services\PlexCrm\PlexPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLeadToTestWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 10;

    public function __construct(public int $formRequestId)
    {
    }

    public function handle(PlexPayloadBuilder $builder): void
    {
        $r = FormRequest::with('site')->findOrFail($this->formRequestId);

        $url = $r->site?->test_webhook_url;
        if (empty($url)) {
            return;
        }

        try {
            $payload = $builder->build($r);
        } catch (\Throwable $e) {
            $payload = [
                'type'   => $r->lead_type,
                'source' => [
                    'dealerId'    => $r->site->plex_dealer_id,
                    'websiteId'   => $r->site->plex_website_id,
                    'websiteHost' => $r->site->domain,
                ],
                'values' => $r->getFormData(),
            ];
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        Log::info('Test webhook sent', [
            'form_request_id' => $r->id,
            'url'             => $url,
            'status'          => $response->status(),
            'payload'         => $payload,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Test webhook failed', [
            'form_request_id' => $this->formRequestId,
            'error'           => $e->getMessage(),
        ]);
    }
}
