<?php

namespace App\Services\PlexCrm;

use App\Enums\CrmProvider;
use App\Jobs\SendLeadToDrivePort;
use App\Jobs\SendLeadToPlexCrm;
use App\Jobs\SendLeadToTestWebhook;
use App\Models\FormRequest;

class PlexLeadDispatcher
{
    public function dispatch(FormRequest $r): void
    {
        $r->loadMissing('site');

        $webhookReady = $r->site && $r->site->hasTestWebhook();

        // Уже доставленную заявку повторно в CRM не шлём и её статус не трогаем.
        if ($r->crm_status !== 'sent') {
            $this->dispatchToCrm($r);
        }

        if ($webhookReady) {
            SendLeadToTestWebhook::dispatch($r->id);
        }
    }

    private function dispatchToCrm(FormRequest $r): void
    {
        $site = $r->site;

        if (! $site || ! $site->isCrmConfigured()) {
            $r->markCrmSkipped();
            return;
        }

        match ($site->crmProvider()) {
            CrmProvider::DrivePort => SendLeadToDrivePort::dispatch($r->id),
            CrmProvider::Plex      => $r->lead_type !== null
                ? SendLeadToPlexCrm::dispatch($r->id)
                : $r->markCrmSkipped(),
        };
    }
}
