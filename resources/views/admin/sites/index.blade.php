@extends('admin.layout')

@section('title', 'Сайты')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Сайты</h2>
    <a href="{{ route('admin.sites.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Добавить сайт
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($sites->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Домен</th>
                            <th>Статус</th>
                            <th>Заявок</th>
                            <th>API ключ</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sites as $site)
                            <tr>
                                <td>#{{ $site->id }}</td>
                                <td>
                                    <strong>{{ $site->name }}</strong>
                                </td>
                                <td>
                                    <code>{{ $site->domain }}</code>
                                </td>
                                <td>
                                    @if($site->is_active)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-secondary">Неактивен</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $site->requests_count }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <code class="me-2" style="font-size: 0.8rem;">{{ substr($site->api_key, 0, 20) }}...</code>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="copyToClipboard('{{ $site->api_key }}')"
                                                title="Копировать API ключ">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $site->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.sites.show', $site) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.sites.edit', $site) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" 
                                              class="d-inline" onsubmit="return confirm('Удалить сайт?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
                Показано {{ $sites->firstItem() ?? 0 }} - {{ $sites->lastItem() ?? 0 }} из {{ $sites->total() }} сайтов
                @if($sites->hasPages())
                    (страница {{ $sites->currentPage() }} из {{ $sites->lastPage() }})
                @endif
            </div>
            
            <!-- Пагинация -->
            <div class="d-flex justify-content-center mt-3">
                {{ $sites->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-globe text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-muted">Сайты не найдены</h4>
                <p class="text-muted">Создайте первый сайт для начала работы</p>
                <a href="{{ route('admin.sites.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Добавить сайт
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Показываем уведомление
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '1050';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Успешно</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    API ключ скопирован в буфер обмена
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    });
}
</script>
@endpush
