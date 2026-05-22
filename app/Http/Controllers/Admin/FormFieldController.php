<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Site $site)
    {
        $fields = $site->fields()->orderBy('order')->get();
        return view('admin.sites.fields.index', compact('site', 'fields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Site $site)
    {
        $usedPlexKeys = $site->fields()
            ->whereNotNull('plex_key')
            ->pluck('plex_key')
            ->all();

        return view('admin.sites.fields.create', compact('site', 'usedPlexKeys'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Site $site)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:form_fields,name,NULL,id,site_id,' . $site->id,
            'plex_key' => [
                'nullable', 'string', 'max:64',
                Rule::unique('form_fields', 'plex_key')->where(fn ($q) => $q->where('site_id', $site->id)),
            ],
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,email,phone,textarea,select,checkbox,radio,file,number,date,url',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'order' => 'nullable|integer|min:0'
        ]);

        // Определяем порядок
        $order = $request->input('order') ?? $site->fields()->max('order') + 1;

        $field = FormField::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'plex_key' => $request->input('plex_key') ?: null,
            'label' => $request->label,
            'type' => $request->type,
            'required' => $request->boolean('required'),
            'placeholder' => $request->placeholder,
            'options' => $request->input('options', []),
            'validation_rules' => $request->input('validation_rules', []),
            'order' => $order,
            'is_active' => true
        ]);

        return redirect()->route('admin.sites.fields.index', $site)
            ->with('success', 'Поле успешно создано');
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site, FormField $field)
    {
        return view('admin.sites.fields.show', compact('site', 'field'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site, FormField $field)
    {
        $usedPlexKeys = $site->fields()
            ->whereNotNull('plex_key')
            ->where('id', '!=', $field->id)
            ->pluck('plex_key')
            ->all();

        return view('admin.sites.fields.edit', compact('site', 'field', 'usedPlexKeys'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site, FormField $field)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:form_fields,name,' . $field->id . ',id,site_id,' . $site->id,
            'plex_key' => [
                'nullable', 'string', 'max:64',
                Rule::unique('form_fields', 'plex_key')
                    ->where(fn ($q) => $q->where('site_id', $site->id))
                    ->ignore($field->id),
            ],
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,email,phone,textarea,select,checkbox,radio,file,number,date,url',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'order' => 'nullable|integer|min:0'
        ]);

        $field->update([
            'name' => $request->name,
            'plex_key' => $request->input('plex_key') ?: null,
            'label' => $request->label,
            'type' => $request->type,
            'required' => $request->boolean('required'),
            'placeholder' => $request->placeholder,
            'options' => $request->input('options', []),
            'validation_rules' => $request->input('validation_rules', []),
            'order' => $request->input('order', $field->order),
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.sites.fields.index', $site)
            ->with('success', 'Поле успешно обновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site, FormField $field)
    {
        $field->delete();
        
        return redirect()->route('admin.sites.fields.index', $site)
            ->with('success', 'Поле успешно удалено');
    }
    
    /**
     * Обновить порядок полей
     */
    public function updateOrder(Request $request, Site $site)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:form_fields,id',
            'fields.*.order' => 'required|integer|min:0'
        ]);
        
        foreach ($request->fields as $fieldData) {
            FormField::where('id', $fieldData['id'])
                ->where('site_id', $site->id)
                ->update(['order' => $fieldData['order']]);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Переключить активность поля
     */
    public function toggleActive(Site $site, FormField $field)
    {
        $field->update(['is_active' => !$field->is_active]);
        
        return redirect()->back()
            ->with('success', 'Статус поля изменен');
    }
}
