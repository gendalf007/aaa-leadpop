@extends('admin.layout')

@section('title', 'Заявки')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Заявки</h2>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.requests.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Поиск</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Имя, телефон, email...">
            </div>
            
            <div class="col-md-2">
                <label for="site_id" class="form-label">Сайт</label>
                <select class="form-select" id="site_id" name="site_id">
                    <option value="">Все сайты</option>
                    @foreach(\App\Models\Site::all() as $site)
                        <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="user_id" class="form-label">Пользователь</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Все пользователи</option>
                    @foreach(\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->getDisplayName() }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">Дата с</label>
                <input type="date" class="form-control" id="date_from" name="date_from"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="time_from" class="form-label">Время с</label>
                <input type="time" class="form-control" id="time_from" name="time_from"
                       value="{{ request('time_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Дата по</label>
                <input type="date" class="form-control" id="date_to" name="date_to"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2">
                <label for="time_to" class="form-label">Время по</label>
                <input type="time" class="form-control" id="time_to" name="time_to"
                       value="{{ request('time_to') }}">
            </div>
            
            <div class="col-md-2">
                <label for="per_page" class="form-label">На странице</label>
                <select class="form-select" id="per_page" name="per_page">
                    @foreach([20, 50, 100, 200, 500] as $pp)
                        <option value="{{ $pp }}" {{ (int) request('per_page', 20) === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Фильтровать
                </button>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Сбросить
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Список заявок -->
<div class="card">
    <div class="card-body">
        @if($requests->count() > 0)
            <form method="POST" action="{{ route('admin.requests.bulk-send') }}"
                  onsubmit="return confirm('Переотправить выбранные заявки в CRM?')">
                @csrf
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-2"></i>Отправить выбранные в CRM
                    </button>
                </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="check-all" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Сайт</th>
                            <th>Пользователь</th>
                            <th>Имя</th>
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Источник</th>
                            <th>IP адрес</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $request->id }}" class="form-check-input row-check">
                                </td>
                                <td>#{{ $request->id }}</td>
                                <td>
                                    @if($request->site)
                                        <span class="badge bg-secondary">{{ $request->site->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->user)
                                        <span class="badge bg-info">{{ $request->user->getDisplayName() }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->getName() }}</strong>
                                </td>
                                <td>{{ $request->getPhone() }}</td>
                                <td>{{ $request->getFieldValue('email') }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $request->getSource() }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $request->ip_address }}</small>
                                </td>
                                <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.requests.show', $request) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </form>
            <script>
                document.getElementById('check-all')?.addEventListener('change', function(e) {
                    document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
                });
            </script>

            <!-- Информация о пагинации -->
            <div class="pagination-info">
                <i class="bi bi-info-circle me-2"></i>
                Показано {{ $requests->firstItem() ?? 0 }} - {{ $requests->lastItem() ?? 0 }} из {{ $requests->total() }} заявок
                @if($requests->hasPages())
                    (страница {{ $requests->currentPage() }} из {{ $requests->lastPage() }})
                @endif
            </div>
            
            <!-- Пагинация -->
            <div class="d-flex justify-content-center mt-3">
                {{ $requests->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-envelope text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-muted">Заявки не найдены</h4>
                <p class="text-muted">Попробуйте изменить параметры фильтрации</p>
            </div>
        @endif
    </div>
</div>
@endsection
