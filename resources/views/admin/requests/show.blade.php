@extends('admin.layout')

@section('title', 'Просмотр заявки')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Заявка #{{ $request->id }}</h2>
    <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Назад к списку
    </a>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Данные заявки</h5>
            </div>
            <div class="card-body">
                @php $formData = $request->getFormData() @endphp
                
                @if(count($formData) > 0)
                    <div class="row">
                        @foreach($formData as $fieldName => $fieldValue)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">{{ ucfirst($fieldName) }}</label>
                                <div class="form-control-plaintext">
                                    @if(is_array($fieldValue))
                                        {{ implode(', ', $fieldValue) }}
                                    @else
                                        {{ $fieldValue ?: 'Не указано' }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Данные формы не найдены</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Метаданные -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Метаданные</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>#{{ $request->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Сайт:</strong></td>
                        <td>
                            @if($request->site)
                                <span class="badge bg-secondary">{{ $request->site->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Пользователь:</strong></td>
                        <td>
                            @if($request->user)
                                <span class="badge bg-info">{{ $request->user->name }}</span>
                                <br>
                                <small class="text-muted">{{ $request->user->getDisplayName() }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Источник:</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $request->getSource() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>IP адрес:</strong></td>
                        <td><code>{{ $request->ip_address }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Дата создания:</strong></td>
                        <td>{{ $request->created_at->format('d.m.Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Обновлено:</strong></td>
                        <td>{{ $request->updated_at->format('d.m.Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- User Agent -->
        @if($request->user_agent)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Agent</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ $request->user_agent }}</small>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Сырые данные -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Сырые данные (JSON)</h5>
    </div>
    <div class="card-body">
        <pre class="bg-light p-3 rounded"><code>{{ json_encode($request->form_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
    </div>
</div>
@endsection
