@extends('admin.layout')

@section('title', 'Создать сайт')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Создать сайт</h2>
    <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Назад к списку
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.sites.store') }}">
            @csrf
            
            <div class="row">
                <!-- Основная информация -->
                <div class="col-md-6">
                    <h5 class="mb-3">Основная информация</h5>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Название сайта *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="domain" class="form-label">Домен *</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                               id="domain" name="domain" value="{{ old('domain') }}" 
                               placeholder="example.com" required>
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Без http:// или https://</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Сайт активен
                            </label>
                        </div>
                    </div>
                </div>

                @include('admin.sites._plex_fields', ['site' => null])
            </div>

            <hr>

            <div class="row">
                <!-- Дизайн -->
                <div class="col-md-6">
                    <h5 class="mb-3">Дизайн</h5>
                    
                    <div class="mb-3">
                        <label for="primary_color" class="form-label">Основной цвет</label>
                        <input type="color" class="form-control form-control-color" id="primary_color" 
                               name="design[primary_color]" value="{{ old('design.primary_color', '#667eea') }}">
                    </div>

                    <div class="mb-3">
                        <label for="secondary_color" class="form-label">Дополнительный цвет</label>
                        <input type="color" class="form-control form-control-color" id="secondary_color" 
                               name="design[secondary_color]" value="{{ old('design.secondary_color', '#764ba2') }}">
                    </div>

                    <div class="mb-3">
                        <label for="button_style" class="form-label">Стиль кнопки</label>
                        <select class="form-select" id="button_style" name="design[button_style]">
                            <option value="gradient" {{ old('design.button_style', 'gradient') == 'gradient' ? 'selected' : '' }}>Градиент</option>
                            <option value="solid" {{ old('design.button_style') == 'solid' ? 'selected' : '' }}>Сплошной</option>
                            <option value="outline" {{ old('design.button_style') == 'outline' ? 'selected' : '' }}>Контур</option>
                        </select>
                    </div>
                </div>

                <!-- Уведомления -->
                <div class="col-md-6">
                    <h5 class="mb-3">Уведомления</h5>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_enabled" name="notifications[email][enabled]" 
                                   value="1" {{ old('notifications.email.enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_enabled">
                                Email уведомления
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email_recipients" class="form-label">Email получатели</label>
                        <input type="text" class="form-control" id="email_recipients" 
                               name="notifications[email][recipients]" 
                               value="{{ old('notifications.email.recipients') }}" 
                               placeholder="manager@example.com, admin@example.com">
                        <div class="form-text">Через запятую</div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary me-2">Отмена</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Создать сайт
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


