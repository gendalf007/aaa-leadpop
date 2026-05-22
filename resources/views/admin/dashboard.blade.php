@extends('admin.layout')

@section('title', 'Дашборд')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Дашборд</h2>
    <div class="text-muted">{{ now()->format('d.m.Y H:i') }}</div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-globe text-primary" style="font-size: 2rem;"></i>
                <h4 class="mt-2">{{ $totalSites }}</h4>
                <p class="text-muted mb-0">Всего сайтов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                <h4 class="mt-2">{{ $activeSites }}</h4>
                <p class="text-muted mb-0">Активных сайтов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-envelope text-info" style="font-size: 2rem;"></i>
                <h4 class="mt-2">{{ $totalRequests }}</h4>
                <p class="text-muted mb-0">Всего заявок</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-calendar-event text-warning" style="font-size: 2rem;"></i>
                <h4 class="mt-2">{{ $recentRequests }}</h4>
                <p class="text-muted mb-0">Заявок за 30 дней</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- График заявок -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Заявки за последние 7 дней</h5>
            </div>
            <div class="card-body">
                <canvas id="requestsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Топ сайтов -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Топ сайтов по заявкам</h5>
            </div>
            <div class="card-body">
                @if($topSites->count() > 0)
                    @foreach($topSites as $site)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $site->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $site->domain }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $site->requests_count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Нет данных</p>
                @endif
            </div>
        </div>

            <!-- Топ пользователей -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Топ пользователей по заявкам</h5>
            </div>
            <div class="card-body">
                @if($topUsers->count() > 0)
                    @foreach($topUsers as $user)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $user->getDisplayName() }}</small>
                            </div>
                            <span class="badge bg-success">{{ $user->form_requests_count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Нет данных</p>
                @endif
            </div>
        </div>

    </div>
    
</div>

<!-- Последние заявки -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Последние заявки</h5>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-primary">Все заявки</a>
            </div>
            <div class="card-body">
                @if($latestRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Сайт</th>
                                    <th>Пользователь</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Источник</th>
                                    <th>Дата</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestRequests as $request)
                                    <tr>
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
                                                <span class="badge bg-info">{{ $request->user->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->getName() }}</td>
                                        <td>{{ $request->getPhone() }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $request->getSource() }}</span>
                                        </td>
                                        <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $request) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Нет заявок</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('requestsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($dailyStats, 'date')),
            datasets: [{
                label: 'Заявки',
                data: @json(array_column($dailyStats, 'count')),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
