@extends('admin.layout')

@section('title', 'Поля формы')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Поля формы: {{ $site->name }}</h2>
        <p class="text-muted mb-0">{{ $site->domain }}</p>
    </div>
    <div>
        <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-2"></i>Назад к сайту
        </a>
        <a href="{{ route('admin.sites.fields.create', $site) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Добавить поле
        </a>
    </div>
</div>

@php $missingPlex = $site->missingRequiredPlexFields ?? $site->missingRequiredPlexKeys(); @endphp
@if(!empty($missingPlex))
    <div class="alert alert-warning">
        <strong>Plex CRM:</strong> для отправки заявок обязательно нужны поля с ключами
        @foreach($missingPlex as $key)
            <code>{{ $key }}</code>@if(!$loop->last), @endif
        @endforeach.
        Добавьте поля с этими значениями в колонке «Ключ Plex CRM», иначе заявки будут уходить со статусом <code>skipped</code>.
    </div>
@endif

<div class="card">
    <div class="card-body">
        @if($fields->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="fieldsTable">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Поле</th>
                            <th>Plex CRM</th>
                            <th>Тип</th>
                            <th>Обязательное</th>
                            <th>Статус</th>
                            <th>Порядок</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="sortableFields">
                        @foreach($fields as $field)
                            <tr data-field-id="{{ $field->id }}" class="field-row">
                                <td>
                                    <i class="bi bi-grip-vertical text-muted handle" style="cursor: move;"></i>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $field->label }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $field->name }}</small>
                                        @if($field->placeholder)
                                            <br>
                                            <small class="text-info">Placeholder: {{ $field->placeholder }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($field->plex_key)
                                        <code>values.{{ $field->plex_key }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $field->type }}</span>
                                    @if($field->hasOptions())
                                        <br>
                                        <small class="text-muted">{{ count($field->getOptions()) }} опций</small>
                                    @endif
                                </td>
                                <td>
                                    @if($field->required)
                                        <span class="badge bg-danger">Да</span>
                                    @else
                                        <span class="badge bg-secondary">Нет</span>
                                    @endif
                                </td>
                                <td>
                                    @if($field->is_active)
                                        <span class="badge bg-success">Активно</span>
                                    @else
                                        <span class="badge bg-warning">Неактивно</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $field->order }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.sites.fields.edit', [$site, $field]) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.sites.fields.toggle-active', [$site, $field]) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Переключить статус">
                                                <i class="bi bi-toggle-{{ $field->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.sites.fields.destroy', [$site, $field]) }}" 
                                              class="d-inline" onsubmit="return confirm('Удалить поле?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button type="button" class="btn btn-success" onclick="saveOrder()">
                    <i class="bi bi-check-circle me-2"></i>Сохранить порядок
                </button>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-input-cursor text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-muted">Поля формы не настроены</h4>
                <p class="text-muted">Создайте первое поле для начала работы</p>
                <a href="{{ route('admin.sites.fields.create', $site) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Добавить поле
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Предпросмотр формы -->
@if($fields->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Предпросмотр формы</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="border rounded p-3 bg-light">
                    <h6>{{ $site->name }}</h6>
                    <p class="text-muted small">Оставьте заявку и мы свяжемся с вами</p>
                    
                    @foreach($fields->where('is_active', true) as $field)
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $field->label }}
                                @if($field->required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            
                            @switch($field->type)
                                @case('text')
                                @case('email')
                                @case('phone')
                                    <input type="{{ $field->type === 'phone' ? 'tel' : $field->type }}" 
                                           class="form-control" 
                                           placeholder="{{ $field->placeholder }}" 
                                           disabled>
                                    @break
                                    
                                @case('textarea')
                                    <textarea class="form-control" rows="3" 
                                              placeholder="{{ $field->placeholder }}" disabled></textarea>
                                    @break
                                    
                                @case('select')
                                    <select class="form-select" disabled>
                                        <option>{{ $field->placeholder ?: 'Выберите...' }}</option>
                                        @foreach($field->getOptions() as $option)
                                            <option>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                    
                                @case('radio')
                                    @foreach($field->getOptions() as $option)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" disabled>
                                            <label class="form-check-label">{{ $option['label'] }}</label>
                                        </div>
                                    @endforeach
                                    @break
                                    
                                @case('checkbox')
                                    @foreach($field->getOptions() as $option)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" disabled>
                                            <label class="form-check-label">{{ $option['label'] }}</label>
                                        </div>
                                    @endforeach
                                    @break
                                    
                                @default
                                    <input type="text" class="form-control" 
                                           placeholder="{{ $field->placeholder }}" disabled>
                            @endswitch
                        </div>
                    @endforeach
                    
                    <button type="button" class="btn btn-primary w-100" disabled>
                        <i class="bi bi-send me-2"></i>Отправить заявку
                    </button>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="alert alert-info">
                    <h6>Информация о форме</h6>
                    <ul class="mb-0">
                        <li>Активных полей: {{ $fields->where('is_active', true)->count() }}</li>
                        <li>Обязательных полей: {{ $fields->where('required', true)->count() }}</li>
                        <li>URL формы: <code>{{ url($site->domain) }}</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Drag & Drop для изменения порядка
    const tbody = document.getElementById('sortableFields');
    if (tbody) {
        new Sortable(tbody, {
            handle: '.handle',
            animation: 150,
            onEnd: function() {
                // Обновляем номера строк
                updateRowNumbers();
            }
        });
    }
});

function updateRowNumbers() {
    const rows = document.querySelectorAll('.field-row');
    rows.forEach((row, index) => {
        const orderCell = row.querySelector('td:nth-child(6) .badge');
        if (orderCell) {
            orderCell.textContent = index + 1;
        }
    });
}

function saveOrder() {
    const rows = document.querySelectorAll('.field-row');
    const fields = [];
    
    rows.forEach((row, index) => {
        const fieldId = row.dataset.fieldId;
        fields.push({
            id: fieldId,
            order: index + 1
        });
    });
    
    fetch('{{ route("admin.sites.fields.update-order", $site) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ fields: fields })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показываем уведомление
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '1050';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header">
                        <strong class="me-auto">Успешно</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        Порядок полей сохранен
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Ошибка при сохранении порядка:', error);
        alert('Ошибка при сохранении порядка');
    });
}
</script>
@endpush
