<?php

namespace App\Services\PlexCrm;

use App\Models\FormRequest;
use App\Services\PlexCrm\Exceptions\PlexConfigException;

class PlexPayloadBuilder
{
    public function build(FormRequest $r): array
    {
        $type = $r->leadType()?->value
            ?? throw new PlexConfigException("FormRequest #{$r->id}: lead_type is not set");

        $site = $r->site;
        if ($site === null) {
            throw new PlexConfigException("FormRequest #{$r->id}: site missing");
        }

        $source = array_filter([
            'dealerId'    => $site->plex_dealer_id !== null ? (int) $site->plex_dealer_id : null,
            'websiteId'   => $site->plex_website_id !== null ? (int) $site->plex_website_id : null,
            'websiteHost' => $site->domain ?: null,
        ], fn ($v) => $v !== null && $v !== '');

        $values = [];
        $fields = $site->fields()
            ->where('is_active', true)
            ->whereNotNull('plex_key')
            ->where('plex_key', '!=', '')
            ->orderBy('order')
            ->get();

        foreach ($fields as $field) {
            $value = $r->getFieldValue($field->name);
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if ($field->plex_key === 'clientPhone') {
                $value = $this->formatPhone((string) $value);
            }
            $values[$field->plex_key] = $value;
        }

        return [
            'type'   => $type,
            'source' => $source,
            'values' => $values,
        ];
    }

    private function formatPhone(string $phone): string
    {
        if ($phone === '') {
            return '';
        }
        return str_starts_with($phone, '+') ? $phone : '+' . $phone;
    }
}
