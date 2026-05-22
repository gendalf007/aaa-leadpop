<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormRequest;
use App\Models\Site;
use App\Services\PlexCrm\PlexLeadDispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    public function submit(Request $request, PlexLeadDispatcher $dispatcher)
    {
        $site = $request->get('site');
        $fields = $site->activeFields()->get();

        $rules = [];
        foreach ($fields as $field) {
            $rules[$field->name] = $field->getValidationRules();
        }

        $validated = $request->validate($rules);

        if (isset($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        $leadType = $this->resolveLeadType($request, $site);

        $formRequest = FormRequest::create([
            'site_id'    => $site->id,
            'user_id'    => auth()->id(),
            'form_data'  => $validated,
            'source'     => $request->source ?? 'api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'lead_type'  => $leadType,
        ]);

        $dispatcher->dispatch($formRequest);

        return response()->json([
            'success' => true,
            'id'      => $formRequest->id,
            'message' => 'Заявка успешно отправлена',
        ]);
    }

    public function getSiteConfig($domain)
    {
        $site = Site::where('domain', $domain)
            ->where('is_active', true)
            ->first();

        if (! $site) {
            return response()->json(['error' => 'Сайт не найден'], 404);
        }

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
