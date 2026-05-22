<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Профиль - {{ $site?->name ?? 'нет сайта' }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, {{ $site?->getDesignSettings()['primary_color'] ?? '#667eea' }} 0%, {{ $site?->getDesignSettings()['secondary_color'] ?? '#764ba2' }} 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-title {
            color: #333;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, {{ $site?->getDesignSettings()['primary_color'] ?? '#667eea' }} 0%, {{ $site?->getDesignSettings()['secondary_color'] ?? '#764ba2' }} 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-12">
                <div class="profile-container p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="profile-title">{{ $site?->name ?? 'Профиль' }}{{ $site ? ' - Профиль' : '' }}</h2>
                        <div>
                            <a href="/" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-house me-2"></i>Главная
                            </a>
                            <form method="POST" action="{{ route('site.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Информация о пользователе</h5>
                                </div>
                                <div class="card-body">
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
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Текущий сайт</h5>
                                </div>
                                <div class="card-body">
                                    @if($site)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Название:</strong></div>
                                            <div class="col-sm-8">{{ $site->name }}</div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Домен:</strong></div>
                                            <div class="col-sm-8">{{ $site->domain }}</div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Статус:</strong></div>
                                            <div class="col-sm-8">
                                                @if($site->is_active)
                                                    <span class="badge bg-success">Активен</span>
                                                @else
                                                    <span class="badge bg-secondary">Неактивен</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted">У вас пока нет доступных сайтов.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($accessibleSites->count() > 1)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Доступные сайты</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($accessibleSites as $accessibleSite)
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                                    <div>
                                                        <strong>{{ $accessibleSite->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $accessibleSite->domain }}</small>
                                                    </div>
                                                    <div>
                                                        @if($site && $accessibleSite->id === $site->id)
                                                            <span class="badge bg-primary">Текущий</span>
                                                        @else
                                                            <a href="http://{{ $accessibleSite->domain }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                Перейти
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
