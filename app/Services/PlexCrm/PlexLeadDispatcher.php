<?php

namespace App\Services\PlexCrm;

use App\Jobs\SendLeadToPlexCrm;
use App\Jobs\SendLeadToTestWebhook;
use App\Models\FormRequest;

class PlexLeadDispatcher
{
    public function dispatch(FormRequest $r): void
    {
        $r->loadMissing('site');

        $plexReady = $r->site && $r->site->isPlexConfigured() && $r->lead_type !== null;
        $webhookReady = $r->site && $r->site->hasTestWebhook();

        if (! $plexReady) {
            $r->markCrmSkipped();
        }

        if ($plexReady) {
            SendLeadToPlexCrm::dispatch($r->id);
        }

        if ($webhookReady) {
            SendLeadToTestWebhook::dispatch($r->id);
        }
    }
}
