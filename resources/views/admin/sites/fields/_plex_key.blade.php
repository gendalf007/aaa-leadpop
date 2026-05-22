@php
    $field = $field ?? null;
    $plexKeyValue = old('plex_key', $field?->plex_key);
    $usedPlexKeys = $usedPlexKeys ?? [];

    // Шаблоны: ключ Plex → метаданные. Описание включает тип поля и обязательность.
    $plexPresets = [
        'clientName' => [
            'option_label' => 'ФИО (обязательное) — текстовое поле',
            'name'     => 'name',
            'label'    => 'ФИО',
            'type'     => 'text',
            'required' => true,
        ],
        'clientFirstName' => [
            'option_label' => 'Имя — текстовое поле',
            'name'     => 'first_name',
            'label'    => 'Имя',
            'type'     => 'text',
            'required' => false,
        ],
        'clientLastName' => [
            'option_label' => 'Фамилия — текстовое поле',
            'name'     => 'last_name',
            'label'    => 'Фамилия',
            'type'     => 'text',
            'required' => false,
        ],
        'clientSecondName' => [
            'option_label' => 'Отчество — текстовое поле',
            'name'     => 'second_name',
            'label'    => 'Отчество',
            'type'     => 'text',
            'required' => false,
        ],
        'clientPhone' => [
            'option_label' => 'Телефон (обязательный) — поле телефона',
            'name'     => 'phone',
            'label'    => 'Телефон',
            'type'     => 'phone',
            'required' => true,
        ],
        'clientBirthDate' => [
            'option_label' => 'Дата рождения — поле даты',
            'name'     => 'birth_date',
            'label'    => 'Дата рождения',
            'type'     => 'date',
            'required' => false,
        ],
        'clientRegion' => [
            'option_label' => 'Регион регистрации — текстовое поле',
            'name'     => 'region',
            'label'    => 'Регион регистрации',
            'type'     => 'text',
            'required' => false,
        ],
        'clientResidenceRegion' => [
            'option_label' => 'Регион проживания — текстовое поле',
            'name'     => 'residence_region',
            'label'    => 'Регион проживания',
            'type'     => 'text',
            'required' => false,
        ],
        'comment' => [
            'option_label' => 'Комментарий — многострочный текст',
            'name'     => 'comment',
            'label'    => 'Комментарий',
            'type'     => 'textarea',
            'required' => false,
        ],
    ];

    $isKnownPreset = $plexKeyValue !== null && array_key_exists($plexKeyValue, $plexPresets);
@endphp

<div class="mb-3">
    <label for="plex_template" class="form-label">Шаблон поля</label>
    <select class="form-select" id="plex_template">
        <option value="">— Своё поле (без отправки в Plex) —</option>
        @foreach($plexPresets as $key => $preset)
            @if(!in_array($key, $usedPlexKeys, true))
                <option value="{{ $key }}" {{ $isKnownPreset && $plexKeyValue === $key ? 'selected' : '' }}>
                    {{ $preset['option_label'] }}
                </option>
            @endif
        @endforeach
    </select>
    <input type="hidden" id="plex_key" name="plex_key" value="{{ $plexKeyValue }}">
    <div class="form-text">
        Выберите шаблон — техническое имя, название и тип поля заполнятся автоматически.<br>
        <strong>Обязательно</strong> должны быть размечены поля <strong>ФИО</strong> и <strong>Телефон</strong> — без них заявка не уйдёт в Plex.
    </div>
    @error('plex_key')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
(function () {
    const presets = @json($plexPresets, JSON_UNESCAPED_UNICODE);
    const templateSel = document.getElementById('plex_template');
    const plexKeyHid  = document.getElementById('plex_key');
    const nameInput   = document.getElementById('name');
    const labelInput  = document.getElementById('label');
    const typeSelect  = document.getElementById('type');
    const requiredCb  = document.getElementById('required');

    if (!templateSel || !plexKeyHid) return;

    function applyPreset(key) {
        if (!key || !presets[key]) {
            plexKeyHid.value = '';
            return;
        }
        plexKeyHid.value = key;
        const preset = presets[key];

        if (nameInput)  { nameInput.value  = preset.name; }
        if (labelInput) { labelInput.value = preset.label; }
        if (typeSelect) {
            typeSelect.value = preset.type;
            typeSelect.dispatchEvent(new Event('change'));
        }
        if (requiredCb) {
            requiredCb.checked = !!preset.required;
            requiredCb.dispatchEvent(new Event('change'));
        }
    }

    templateSel.addEventListener('change', () => applyPreset(templateSel.value));
})();
</script>
@endpush
