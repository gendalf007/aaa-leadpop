<?php

namespace App\Models;

use App\Enums\PlexLeadType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'api_key',
        'is_active',
        'settings',
        'plex_dealer_id',
        'plex_website_id',
        'allowed_lead_types',
        'default_lead_type',
        'send_to_crm',
        'test_webhook_url',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'allowed_lead_types' => 'array',
        'send_to_crm' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            if (empty($site->api_key)) {
                $site->api_key = Str::random(64);
            }
        });
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function requests()
    {
        return $this->hasMany(FormRequest::class);
    }

    public function activeFields()
    {
        return $this->fields()->where('is_active', true)->orderBy('order');
    }

    public function getDesignSettings()
    {
        return $this->settings['design'] ?? [];
    }

    public function getNotificationSettings()
    {
        return $this->settings['notifications'] ?? [];
    }

    public function getValidationSettings()
    {
        return $this->settings['validation'] ?? [];
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sites');
    }

    public function getAccessibleUsers()
    {
        return $this->users;
    }

    /**
     * @return array<int, PlexLeadType>
     */
    public function allowedLeadTypes(): array
    {
        $keys = (array) ($this->allowed_lead_types ?? []);
        $out = [];
        foreach ($keys as $key) {
            $case = PlexLeadType::tryFrom((string) $key);
            if ($case !== null) {
                $out[] = $case;
            }
        }
        return $out;
    }

    public function defaultLeadType(): ?PlexLeadType
    {
        return $this->default_lead_type
            ? PlexLeadType::tryFrom($this->default_lead_type)
            : null;
    }

    public function isPlexConfigured(): bool
    {
        return (bool) $this->send_to_crm
            && ! empty($this->plex_dealer_id)
            && ! empty($this->plex_website_id)
            && ! empty(config('services.plex_crm.token'))
            && $this->allowedLeadTypes() !== []
            && $this->hasRequiredPlexFields();
    }

    public function hasTestWebhook(): bool
    {
        return ! empty($this->test_webhook_url);
    }

    /**
     * В Plex обязательны values.clientName и values.clientPhone —
     * у сайта должно быть хотя бы одно активное поле, размеченное под каждый ключ.
     */
    public function hasRequiredPlexFields(): bool
    {
        $keys = $this->fields()
            ->where('is_active', true)
            ->whereIn('plex_key', ['clientName', 'clientPhone'])
            ->pluck('plex_key')
            ->unique()
            ->all();

        return in_array('clientName', $keys, true)
            && in_array('clientPhone', $keys, true);
    }

    /**
     * @return string[] список отсутствующих обязательных ключей
     */
    public function missingRequiredPlexKeys(): array
    {
        $keys = $this->fields()
            ->where('is_active', true)
            ->whereIn('plex_key', ['clientName', 'clientPhone'])
            ->pluck('plex_key')
            ->unique()
            ->all();

        return array_values(array_diff(['clientName', 'clientPhone'], $keys));
    }
}
