<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlexLeadType;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withCount('requests')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        return view('admin.sites.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);

        $site = Site::create(array_merge(
            $this->extractSiteFields($request, $data),
            ['api_key' => Str::random(64)]
        ));

        $this->createRequiredPlexFields($site);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Сайт успешно создан. API ключ: ' . $site->api_key);
    }

    /**
     * Создать обязательные для Plex CRM поля формы (ФИО и Телефон).
     * Plex принимает заявку только при наличии values.clientName и values.clientPhone.
     */
    private function createRequiredPlexFields(Site $site): void
    {
        $site->fields()->create([
            'name'      => 'name',
            'plex_key'  => 'clientName',
            'label'     => 'ФИО',
            'type'      => 'text',
            'required'  => true,
            'order'     => 1,
            'is_active' => true,
        ]);

        $site->fields()->create([
            'name'      => 'phone',
            'plex_key'  => 'clientPhone',
            'label'     => 'Телефон',
            'type'      => 'phone',
            'required'  => true,
            'order'     => 2,
            'is_active' => true,
        ]);
    }

    public function show(Site $site)
    {
        $site->load(['fields' => function ($query) {
            $query->orderBy('order');
        }]);

        $recentRequests = $site->requests()
            ->latest()
            ->take(20)
            ->get();

        $site->requests_count = $site->requests()->count();
        $site->fields_count = $site->fields()->count();

        return view('admin.sites.show', compact('site', 'recentRequests'));
    }

    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $data = $this->validateInput($request, $site->id);

        $site->update($this->extractSiteFields($request, $data));

        return redirect()->route('admin.sites.index')
            ->with('success', 'Сайт успешно обновлен');
    }

    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('admin.sites.index')
            ->with('success', 'Сайт успешно удален');
    }

    public function regenerateApiKey(Site $site)
    {
        $site->update(['api_key' => Str::random(64)]);

        return redirect()->back()
            ->with('success', 'API ключ обновлен: ' . $site->api_key);
    }

    private function validateInput(Request $request, ?int $siteId = null): array
    {
        $leadValues = PlexLeadType::values();

        return $request->validate([
            'name'   => 'required|string|max:255',
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('sites', 'domain')->ignore($siteId),
            ],
            'plex_dealer_id'      => 'nullable|integer|min:1',
            'plex_website_id'     => 'nullable|integer|min:1',
            'allowed_lead_types'  => 'nullable|array',
            'allowed_lead_types.*' => ['string', Rule::in($leadValues)],
            'default_lead_type'   => ['nullable', 'string', Rule::in($leadValues)],
            'send_to_crm'         => 'nullable|boolean',
            'test_webhook_url'    => 'nullable|url|max:255',
        ]);
    }

    private function extractSiteFields(Request $request, array $data): array
    {
        $allowed = $data['allowed_lead_types'] ?? [];
        $default = $data['default_lead_type'] ?? null;

        if ($default !== null && ! in_array($default, $allowed, true)) {
            $default = null;
        }

        return [
            'name'              => $data['name'],
            'domain'            => $data['domain'],
            'is_active'         => $request->boolean('is_active', true),
            'plex_dealer_id'    => $data['plex_dealer_id'] ?? null,
            'plex_website_id'   => $data['plex_website_id'] ?? null,
            'allowed_lead_types' => $allowed ?: null,
            'default_lead_type' => $default,
            'send_to_crm'       => $request->boolean('send_to_crm'),
            'test_webhook_url'  => $data['test_webhook_url'] ?? null,
            'settings'          => [
                'design' => [
                    'primary_color'   => $request->input('design.primary_color', '#667eea'),
                    'secondary_color' => $request->input('design.secondary_color', '#764ba2'),
                    'button_style'    => $request->input('design.button_style', 'gradient'),
                ],
                'notifications' => [
                    'email' => [
                        'enabled'    => $request->boolean('notifications.email.enabled', false),
                        'recipients' => array_filter(explode(',', (string) $request->input('notifications.email.recipients', ''))),
                    ],
                ],
            ],
        ];
    }
}
