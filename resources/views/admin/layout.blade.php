<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Админка') - Система мультисайта</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin: 2px 0;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-left: 250px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        /* Кастомные стили для пагинации */
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            color: #667eea;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .page-link:hover {
            color: #5a6fd8;
            background-color: #f8f9fa;
            border-color: #667eea;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
        
        .page-item.disabled .page-link:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Стили для информации о пагинации */
        .pagination-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
                margin: 1px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Система мультисайта</h5>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
               href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Дашборд
            </a>
            
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
               href="{{ route('admin.users.index') }}">
                <i class="bi bi-people me-2"></i>
                Пользователи
            </a>
            
            <a class="nav-link {{ request()->routeIs('admin.sites.*') ? 'active' : '' }}" 
               href="{{ route('admin.sites.index') }}">
                <i class="bi bi-globe me-2"></i>
                Сайты
            </a>
            
            <a class="nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}" 
               href="{{ route('admin.requests.index') }}">
                <i class="bi bi-envelope me-2"></i>
                Заявки
            </a>
            
            <hr class="text-white-50">
            
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-start w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Выйти
                </button>
            </form>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
