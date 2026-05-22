<?php

namespace App\Models;

use App\Enums\PlexLeadType;
use Illuminate\Database\Eloquent\Model;

class FormRequest extends Model
{
    protected $fillable = [
        'site_id',
        'user_id',
        'form_data',
        'source',
        'ip_address',
        'user_agent',
        'lead_type',
        'crm_status',
    ];

    protected $casts = [
        'form_data' => 'array',
        'crm_sent_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormData()
    {
        return $this->form_data ?? [];
    }

    public function getFieldValue($fieldName)
    {
        return $this->getFormData()[$fieldName] ?? null;
    }

    public function getName()
    {
        return $this->getFieldValue('name');
    }

    public function getPhone()
    {
        return $this->getFieldValue('phone');
    }

    public function getComment()
    {
        return $this->getFieldValue('comment');
    }

    public function getSource()
    {
        return $this->source ?? 'form';
    }

    public function leadType(): ?PlexLeadType
    {
        return $this->lead_type ? PlexLeadType::tryFrom($this->lead_type) : null;
    }

    public function markCrmSent(?string $externalId): void
    {
        $this->forceFill([
            'crm_status' => 'sent',
            'crm_external_id' => $externalId,
            'crm_sent_at' => now(),
            'crm_last_error' => null,
        ])->save();
    }

    public function markCrmFailed(string $error): void
    {
        $this->forceFill([
            'crm_status' => 'failed',
            'crm_last_error' => $error,
        ])->save();
    }

    public function markCrmSkipped(): void
    {
        $this->forceFill([
            'crm_status' => 'skipped',
        ])->save();
    }

    public function incrementCrmAttempts(): void
    {
        $this->increment('crm_attempts');
    }
}
