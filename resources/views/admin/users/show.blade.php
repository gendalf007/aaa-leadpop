@extends('admin.layout')

@section('title', 'Пользователь')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Пользователь: {{ $user->name }}</h2>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-2"></i>Редактировать
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Основная информация</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>ID:</strong></div>
                    <div class="col-sm-8">{{ $user->id }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Имя:</strong></div>
                    <div class="col-sm-8">{{ $user->name }}</div>
                </div>
                
                @if($user->username)
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Логин:</strong></div>
                    <div class="col-sm-8">{{ $user->username }}</div>
                </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Email:</strong></div>
                    <div class="col-sm-8">{{ $user->email }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Роль:</strong></div>
                    <div class="col-sm-8">
                        @if($user->is_admin)
                            <span class="badge bg-danger">Администратор</span>
                        @else
                            <span class="badge bg-secondary">Пользователь</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Дата регистрации:</strong></div>
                    <div class="col-sm-8">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Последнее обновление:</strong></div>
                    <div class="col-sm-8">{{ $user->updated_at->format('d.m.Y H:i') }}</div>
                </div>
                
                @if($user->id === auth()->id())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Это ваш аккаунт
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Доступ к сайтам</h5>
            </div>
            <div class="card-body">
                @if($user->is_admin)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Администратор имеет доступ ко всем сайтам
                    </div>
                @else
                    @if($accessibleSites->count() > 0)
                        <div class="list-group">
                            @foreach($accessibleSites as $site)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $site->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $site->domain }}</small>
                                    </div>
                                    <span class="badge bg-primary">Доступ</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            У пользователя нет доступа ни к одному сайту
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$user->is_admin && $accessibleSites->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Статистика по сайтам</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Сайт</th>
                                <th>Домен</th>
                                <th>Заявок</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessibleSites as $site)
                                <tr>
                                    <td>
                                        <strong>{{ $site->name }}</strong>
                                    </td>
                                    <td>{{ $site->domain }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $site->requests_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($site->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-secondary">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
