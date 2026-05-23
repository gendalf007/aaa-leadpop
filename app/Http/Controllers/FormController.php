<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use App\Models\Site;
use App\Services\PlexCrm\PlexLeadDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    public function showByDomain($siteDomain)
    {
        $site = Site::where('domain', $siteDomain)->where('is_active', true)->firstOrFail();
        $fields = $site->activeFields()->get();

        return view('form', compact('site', 'fields'));
    }

    public function showBySubdomain($subdomain, $domain)
    {
        $fullDomain = $subdomain . '.' . $domain;
        $site = Site::where('domain', $fullDomain)->where('is_active', true)->firstOrFail();
        $fields = $site->activeFields()->get();

        return view('form', compact('site', 'fields'));
    }

    public function submitForm(Request $request, PlexLeadDispatcher $dispatcher)
    {
        $host = $request->getHost();
        $site = Site::where('domain', $host)->where('is_active', true)->firstOrFail();
        $fields = $site->activeFields()->get();

        $rules = [];
        foreach ($fields as $field) {
            $rules[$field->name] = $field->getValidationRules();
        }

        $validated = $request->validate($rules);

        if (isset($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        if (isset($validated['phone'])) {
            $repat = FormRequest::where('site_id', $site->id)
                ->where('form_data->phone', $validated['phone'])
                ->where('created_at', '>', now()->subDays(2))
                ->first();

            if ($repat) {
                return redirect()->back()->with('error', 'Вы уже отправили заявку с этим номером телефона. Пожалуйста, подождите 2 дня перед отправкой новой заявки.');
            }
        }

        $leadType = $this->resolveLeadType($request, $site);

        $formRequest = FormRequest::create([
            'site_id'    => $site->id,
            'user_id'    => auth()->id(),
            'form_data'  => $validated,
            'source'     => 'form',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'lead_type'  => $leadType,
        ]);

        // Изолируем сабмит от падений Plex/webhook: заявка уже сохранена в БД,
        // а статус доставки трекается на самом FormRequest (failed/skipped/sent).
        // На sync-очереди исключение из job всплыло бы в HTTP-ответ — этого допускать нельзя.
        try {
            $dispatcher->dispatch($formRequest);
        } catch (\Throwable $e) {
            Log::error('Lead dispatch failed', [
                'form_request_id' => $formRequest->id,
                'site_id'         => $formRequest->site_id,
                'error'           => $e->getMessage(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно отправлена',
            ]);
        }

        return redirect()->back()->with('success', 'Заявка успешно отправлена');
    }

    public function getSiteConfig($domain)
    {
        $site = Site::where('domain', $domain)->where('is_active', true)->firstOrFail();
        $fields = $site->activeFields()->get();

        return response()->json([
            'site' => [
                'name'   => $site->name,
                'domain' => $site->domain,
                'design' => $site->getDesignSettings(),
            ],
            'fields' => $fields->map(function ($field) {
                return [
                    'name'        => $field->name,
                    'label'       => $field->label,
                    'type'        => $field->type,
                    'required'    => $field->required,
                    'placeholder' => $field->placeholder,
                    'options'     => $field->getOptions(),
                    'attributes'  => $field->getHtmlAttributes(),
                ];
            }),
        ]);
    }

    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) == 11 && substr($phone, 0, 1) == '8') {
            $phone = '7' . substr($phone, 1);
        }

        if (strlen($phone) == 10) {
            $phone = '7' . $phone;
        }

        return $phone;
    }

    private function resolveLeadType(Request $request, Site $site): ?string
    {
        $allowed = array_map(fn ($t) => $t->value, $site->allowedLeadTypes());

        if (count($allowed) === 0) {
            return null;
        }

        if (count($allowed) === 1) {
            return $allowed[0];
        }

        $validated = $request->validate([
            '_lead_type' => ['required', Rule::in($allowed)],
        ]);

        return $validated['_lead_type'];
    }
}
