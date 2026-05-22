@extends('admin.layout')

@section('title', 'Пользователи')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Пользователи</h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Добавить пользователя
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Сайты</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-info ms-2">Вы</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->username)
                                        <span class="badge bg-secondary">{{ $user->username }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger">Администратор</span>
                                    @else
                                        <span class="badge bg-secondary">Пользователь</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="text-muted">Все сайты</span>
                                    @else
                                        @if($user->sites->count() > 0)
                                            <span class="badge bg-primary">{{ $user->sites->count() }} сайт(ов)</span>
                                        @else
                                            <span class="text-muted">Нет доступа</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Информация о пагинации -->
            <div class="pagination-info">
                <i class="bi bi-info-circle me-2"></i>
                Показано {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} из {{ $users->total() }} пользователей
                @if($users->hasPages())
                    (страница {{ $users->currentPage() }} из {{ $users->lastPage() }})
                @endif
            </div>
            
            <!-- Пагинация -->
            <div class="d-flex justify-content-center mt-3">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">Пользователи не найдены</h5>
                <p class="text-muted">Создайте первого пользователя для начала работы</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Добавить пользователя
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
