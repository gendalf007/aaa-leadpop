@extends('admin.layout')

@section('title', 'Добавить пользователя')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Добавить пользователя</h2>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Назад к списку
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя *</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Логин</label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Необязательно. Если не указан, будет использоваться email</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль *</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input @error('is_admin') is-invalid @enderror" 
                                   id="is_admin" 
                                   name="is_admin" 
                                   value="1" 
                                   {{ old('is_admin') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_admin">
                                Администратор
                            </label>
                            @error('is_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Администраторы имеют доступ ко всем сайтам и функциям системы
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Доступ к сайтам</label>
                        <div class="form-text mb-2">
                            Выберите сайты, к которым у пользователя будет доступ (для администраторов не требуется)
                        </div>
                        
                        @if($sites->count() > 0)
                            <div class="row">
                                @foreach($sites as $site)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="site_{{ $site->id }}" 
                                                   name="sites[]" 
                                                   value="{{ $site->id }}"
                                                   {{ in_array($site->id, old('sites', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="site_{{ $site->id }}">
                                                <strong>{{ $site->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $site->domain }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Нет доступных сайтов. Сначала создайте сайт в разделе "Сайты".
                            </div>
                        @endif
                        
                        @error('sites')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Создать пользователя
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Информация</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Роли пользователей:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-danger">Администратор</span> - полный доступ ко всем функциям</li>
                        <li><span class="badge bg-secondary">Пользователь</span> - доступ только к назначенным сайтам</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Авторизация:</h6>
                    <ul class="list-unstyled">
                        <li>Пользователи могут входить по username или email</li>
                        <li>Если username не указан, используется email</li>
                        <li>Username должен быть уникальным</li>
                        <li><strong>В админку могут входить только администраторы</strong></li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Требования к паролю:</h6>
                    <ul class="list-unstyled">
                        <li>Минимум 8 символов</li>
                        <li>Рекомендуется использовать буквы, цифры и символы</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
