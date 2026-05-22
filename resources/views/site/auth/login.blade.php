<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход - {{ $site->name }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, {{ $site->getDesignSettings()['primary_color'] ?? '#667eea' }} 0%, {{ $site->getDesignSettings()['secondary_color'] ?? '#764ba2' }} 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-title {
            color: #333;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: {{ $site->getDesignSettings()['primary_color'] ?? '#667eea' }};
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, {{ $site->getDesignSettings()['primary_color'] ?? '#667eea' }} 0%, {{ $site->getDesignSettings()['secondary_color'] ?? '#764ba2' }} 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-container p-4">
                    <div class="text-center mb-4">
                        <h2 class="login-title">{{ $site->name }}</h2>
                        <p class="text-muted">Вход в систему</p>
                    </div>
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('site.login.post') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="login" class="form-label">Логин или Email</label>
                            <input type="text" 
                                   class="form-control @error('login') is-invalid @enderror" 
                                   id="login" 
                                   name="login" 
                                   value="{{ old('login') }}" 
                                   required 
                                   autofocus>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="remember" 
                                       name="remember">
                                <label class="form-check-label" for="remember">
                                    Запомнить меня
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Войти
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="/" class="text-muted">Вернуться на главную</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
