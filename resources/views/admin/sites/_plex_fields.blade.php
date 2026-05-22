@php
    use App\Enums\PlexLeadType;
    $site = $site ?? null;
    $currentAllowed = old('allowed_lead_types', $site?->allowed_lead_types ?? []);
    $currentAllowed = is_array($currentAllowed) ? $currentAllowed : [];
    $currentDefault = old('default_lead_type', $site?->default_lead_type);
@endphp

<div class="col-md-6">
    <h5 class="mb-3">Plex CRM</h5>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="send_to_crm" name="send_to_crm" value="1"
                   {{ old('send_to_crm', $site?->send_to_crm ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="send_to_crm">
                <strong>Отправлять заявки в Plex CRM</strong>
            </label>
        </div>
        <div class="form-text">Если выключено — заявки сохраняются в БД, но в Plex не уходят.</div>
    </div>

    <div class="mb-3">
        <label for="test_webhook_url" class="form-label">Тестовый webhook URL</label>
        <input type="url" class="form-control @error('test_webhook_url') is-invalid @enderror"
               id="test_webhook_url" name="test_webhook_url"
               value="{{ old('test_webhook_url', $site?->test_webhook_url) }}"
               maxlength="255"
               placeholder="https://webhook.site/xxxxxxxx">
        <div class="form-text">Если задано — для каждой заявки уходит дополнительный POST с payload'ом (для отладки).</div>
        @error('test_webhook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <hr>

    <div class="mb-3">
        <label for="plex_dealer_id" class="form-label">ID компании в Plex CRM (dealerId)</label>
        <input type="number" min="1" class="form-control @error('plex_dealer_id') is-invalid @enderror"
               id="plex_dealer_id" name="plex_dealer_id"
               value="{{ old('plex_dealer_id', $site?->plex_dealer_id) }}"
               placeholder="Например, 123">
        @error('plex_dealer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="plex_website_id" class="form-label">ID сайта в Plex CRM (websiteId)</label>
        <input type="number" min="1" class="form-control @error('plex_website_id') is-invalid @enderror"
               id="plex_website_id" name="plex_website_id"
               value="{{ old('plex_website_id', $site?->plex_website_id) }}"
               placeholder="Например, 456">
        <div class="form-text">
            Адрес сайта (<code>websiteHost</code>) уходит в Plex автоматически из домена этого сайта
            (<code>{{ $site?->domain ?? '...' }}</code>) — отдельно указывать не нужно.
        </div>
        @error('plex_website_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Допустимые типы заявок</label>
        <div class="row g-2">
            @foreach(PlexLeadType::options() as $key => $label)
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input plex-allowed-type" type="checkbox"
                               id="allowed_lead_types_{{ $key }}"
                               name="allowed_lead_types[]" value="{{ $key }}"
                               data-key="{{ $key }}"
                               data-label="{{ $label }}"
                               {{ in_array($key, $currentAllowed, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowed_lead_types_{{ $key }}">
                            {{ $label }} <span class="text-muted">({{ $key }})</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('allowed_lead_types')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @error('allowed_lead_types.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="default_lead_type" class="form-label">Тип по умолчанию</label>
        <select class="form-select @error('default_lead_type') is-invalid @enderror"
                id="default_lead_type" name="default_lead_type">
            <option value="">— нет —</option>
            @foreach(PlexLeadType::options() as $key => $label)
                <option value="{{ $key }}"
                        data-allowed="{{ in_array($key, $currentAllowed, true) ? '1' : '0' }}"
                        {{ $currentDefault === $key ? 'selected' : '' }}
                        {{ in_array($key, $currentAllowed, true) ? '' : 'disabled' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Доступны только отмеченные выше типы.</div>
        @error('default_lead_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
(function () {
    const select = document.getElementById('default_lead_type');
    if (!select) return;

    function refresh() {
        const allowed = new Set(
            Array.from(document.querySelectorAll('.plex-allowed-type:checked'))
                .map(cb => cb.dataset.key)
        );
        Array.from(select.options).forEach(opt => {
            if (opt.value === '') return;
            const ok = allowed.has(opt.value);
            opt.disabled = !ok;
            if (!ok && opt.selected) {
                opt.selected = false;
                select.value = '';
            }
        });
    }
    document.querySelectorAll('.plex-allowed-type').forEach(cb => cb.addEventListener('change', refresh));
    refresh();
})();
</script>
@endpush
