<?php

namespace App\Services\DrivePort;

use App\Enums\PlexLeadType;
use App\Models\FormRequest;
use App\Services\PlexCrm\PlexPayloadBuilder;

class DrivePortPayloadBuilder
{
    public function __construct(private PlexPayloadBuilder $plex)
    {
    }

    public function build(FormRequest $r): array
    {
        return [
            'type'       => $r->leadType()?->value ?? PlexLeadType::Unknown->value,
            'source'     => ['websiteId' => config('services.drive_port.website_id')],
            'values'     => $this->plex->values($r),
            // Драйв Порт дедуплицирует по externalId — повтор после таймаута не создаст второй лид.
            'externalId' => 'leadpop-' . $r->id,
        ];
    }
}
