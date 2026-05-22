@extends('admin.layout')

@section('title', 'Редактировать поле')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Редактировать поле: {{ $site->name }}</h2>
        <p class="text-muted mb-0">{{ $site->domain }}</p>
    </div>
    <a href="{{ route('admin.sites.fields.index', $site) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Назад к полям
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.sites.fields.update', [$site, $field]) }}" id="fieldForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Основная информация -->
                <div class="col-md-6">
                    <h5 class="mb-3">Основная информация</h5>

                    @include('admin.sites.fields._plex_key', ['field' => $field])

                    <div class="mb-3">
                        <label for="name" class="form-label">Техническое имя *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $field->name) }}" 
                               placeholder="name, phone, email..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Латинские буквы, цифры, подчеркивания. Без пробелов.</div>
                    </div>

                    <div class="mb-3">
                        <label for="label" class="form-label">Отображаемое название *</label>
                        <input type="text" class="form-control @error('label') is-invalid @enderror" 
                               id="label" name="label" value="{{ old('label', $field->label) }}" 
                               placeholder="Имя, Телефон, Email..." required>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Тип поля *</label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="">Выберите тип...</option>
                            <option value="text" {{ old('type', $field->type) == 'text' ? 'selected' : '' }}>Текст</option>
                            <option value="email" {{ old('type', $field->type) == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="phone" {{ old('type', $field->type) == 'phone' ? 'selected' : '' }}>Телефон</option>
                            <option value="textarea" {{ old('type', $field->type) == 'textarea' ? 'selected' : '' }}>Многострочный текст</option>
                            <option value="select" {{ old('type', $field->type) == 'select' ? 'selected' : '' }}>Выпадающий список</option>
                            <option value="radio" {{ old('type', $field->type) == 'radio' ? 'selected' : '' }}>Радиокнопки</option>
                            <option value="checkbox" {{ old('type', $field->type) == 'checkbox' ? 'selected' : '' }}>Чекбоксы</option>
                            <option value="number" {{ old('type', $field->type) == 'number' ? 'selected' : '' }}>Число</option>
                            <option value="date" {{ old('type', $field->type) == 'date' ? 'selected' : '' }}>Дата</option>
                            <option value="url" {{ old('type', $field->type) == 'url' ? 'selected' : '' }}>Ссылка</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="placeholder" class="form-label">Placeholder</label>
                        <input type="text" class="form-control @error('placeholder') is-invalid @enderror" 
                               id="placeholder" name="placeholder" value="{{ old('placeholder', $field->placeholder) }}" 
                               placeholder="Введите подсказку...">
                        @error('placeholder')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="required" name="required" 
                                   value="1" {{ old('required', $field->required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="required">
                                Обязательное поле
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', $field->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Активное поле
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Опции для select/radio/checkbox -->
                <div class="col-md-6">
                    <h5 class="mb-3">Опции поля</h5>
                    
                    <div id="optionsContainer" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Опции</label>
                            <div id="optionsList">
                                <!-- Опции будут добавляться динамически -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                                <i class="bi bi-plus-circle me-1"></i>Добавить опцию
                            </button>
                        </div>
                    </div>

                    <!-- Предпросмотр поля -->
                    <div class="mt-4">
                        <h6>Предпросмотр</h6>
                        <div id="fieldPreview" class="border rounded p-3 bg-light">
                            <p class="text-muted">Выберите тип поля для предпросмотра</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.sites.fields.index', $site) }}" class="btn btn-secondary me-2">Отмена</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Сохранить изменения
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('optionsContainer');
    const fieldPreview = document.getElementById('fieldPreview');
    
    // Загружаем существующие опции
    const existingOptions = @json($field->getOptions());
    if (existingOptions.length > 0) {
        existingOptions.forEach(option => {
            addOption(option.value, option.label);
        });
    }
    
    // Обработчик изменения типа поля
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        updateOptionsVisibility(selectedType);
        updateFieldPreview(selectedType);
    });
    
    // Инициализация
    if (typeSelect.value) {
        updateOptionsVisibility(typeSelect.value);
        updateFieldPreview(typeSelect.value);
    }
});

function updateOptionsVisibility(type) {
    const optionsContainer = document.getElementById('optionsContainer');
    const needsOptions = ['select', 'radio', 'checkbox'].includes(type);
    
    if (needsOptions) {
        optionsContainer.style.display = 'block';
        if (document.querySelectorAll('.option-item').length === 0) {
            addOption();
        }
    } else {
        optionsContainer.style.display = 'none';
    }
}

function updateFieldPreview(type) {
    const fieldPreview = document.getElementById('fieldPreview');
    const label = document.getElementById('label').value || 'Название поля';
    const placeholder = document.getElementById('placeholder').value || '';
    const required = document.getElementById('required').checked;
    
    let previewHtml = `<label class="form-label">${label}`;
    if (required) previewHtml += ' <span class="text-danger">*</span>';
    previewHtml += '</label>';
    
    switch (type) {
        case 'text':
        case 'email':
        case 'phone':
            previewHtml += `<input type="${type === 'phone' ? 'tel' : type}" class="form-control" placeholder="${placeholder}" disabled>`;
            break;
        case 'textarea':
            previewHtml += `<textarea class="form-control" rows="3" placeholder="${placeholder}" disabled></textarea>`;
            break;
        case 'select':
            previewHtml += `<select class="form-select" disabled><option>${placeholder || 'Выберите...'}</option></select>`;
            break;
        case 'radio':
            previewHtml += `<div class="form-check"><input class="form-check-input" type="radio" disabled><label class="form-check-label">Опция 1</label></div>`;
            break;
        case 'checkbox':
            previewHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" disabled><label class="form-check-label">Опция 1</label></div>`;
            break;
        case 'number':
            previewHtml += `<input type="number" class="form-control" placeholder="${placeholder}" disabled>`;
            break;
        case 'date':
            previewHtml += `<input type="date" class="form-control" disabled>`;
            break;
        case 'url':
            previewHtml += `<input type="url" class="form-control" placeholder="${placeholder}" disabled>`;
            break;
        default:
            previewHtml = '<p class="text-muted">Выберите тип поля для предпросмотра</p>';
    }
    
    fieldPreview.innerHTML = previewHtml;
}

function addOption(value = '', label = '') {
    const optionsList = document.getElementById('optionsList');
    const optionIndex = document.querySelectorAll('.option-item').length;
    
    const optionHtml = `
        <div class="option-item border rounded p-2 mb-2">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-sm" 
                           name="options[${optionIndex}][value]" 
                           placeholder="Значение" value="${value}" required>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-sm" 
                           name="options[${optionIndex}][label]" 
                           placeholder="Отображаемый текст" value="${label}" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOption(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    optionsList.insertAdjacentHTML('beforeend', optionHtml);
}

function removeOption(button) {
    button.closest('.option-item').remove();
    reindexOptions();
}

function reindexOptions() {
    const options = document.querySelectorAll('.option-item');
    options.forEach((option, index) => {
        const inputs = option.querySelectorAll('input');
        inputs[0].name = `options[${index}][value]`;
        inputs[1].name = `options[${index}][label]`;
    });
}

// Обновление предпросмотра при изменении полей
document.getElementById('label').addEventListener('input', function() {
    if (document.getElementById('type').value) {
        updateFieldPreview(document.getElementById('type').value);
    }
});

document.getElementById('placeholder').addEventListener('input', function() {
    if (document.getElementById('type').value) {
        updateFieldPreview(document.getElementById('type').value);
    }
});

document.getElementById('required').addEventListener('change', function() {
    if (document.getElementById('type').value) {
        updateFieldPreview(document.getElementById('type').value);
    }
});
</script>
@endpush
