<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Главная страница</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .form-title {
            color: #333;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="form-container p-4">
                    <h2 class="text-center form-title mb-4">{{ config('app.name') }}</h2>
                    
                    @if(session('success'))
                        <div class="alert alert-success mb-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('form.store') }}">
                        @csrf
                                                
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Введите имя" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="+7 (999) 123-45-67" value="{{ old('phone') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-4">
                            <label for="comment" class="form-label">Комментарий</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="4" placeholder="Введите комментарий">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Отправить</button>
                    </form>
                    
                    @if(isset($lastRequest))
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5 class="text-center mb-3">Последняя заявка</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Имя:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Телефон:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_phone }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Комментарий:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_comment }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Дата: {{ $lastRequest->created_at->format('d.m.Y H:i') }} (МСК)</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Маска для телефона с jQuery.mask
            $('#phone').mask('+7 (000) 000-00-00', {
                placeholder: '+7 (___) ___-__-__'
            });
            
            // Анимация появления формы
            $('.form-container').hide().fadeIn(800);
        });
    </script>
</body>
</html> 