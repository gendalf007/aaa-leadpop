<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\FormRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Общая статистика
        $totalSites = Site::count();
        $activeSites = Site::where('is_active', true)->count();
        $totalRequests = FormRequest::count();
        
        // Статистика за последние 30 дней
        $recentRequests = FormRequest::where('created_at', '>=', now()->subDays(30))->count();
        
        // Последние заявки
        $latestRequests = FormRequest::with(['site', 'user'])
            ->latest()
            ->take(10)
            ->get();
        
        // Топ сайтов по заявкам
        $topSites = Site::withCount('requests')
            ->orderBy('requests_count', 'desc')
            ->take(5)
            ->get();
        
        // Топ пользователей по заявкам
        $topUsers = \App\Models\User::withCount('formRequests')
            ->having('form_requests_count', '>', 0)
            ->orderBy('form_requests_count', 'desc')
            ->take(5)
            ->get();
        
        // Статистика по дням (последние 7 дней)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = FormRequest::whereDate('created_at', $date)->count();
            $dailyStats[] = [
                'date' => now()->subDays($i)->format('d.m'),
                'count' => $count
            ];
        }
        
        return view('admin.dashboard', compact(
            'totalSites',
            'activeSites', 
            'totalRequests',
            'recentRequests',
            'latestRequests',
            'topSites',
            'topUsers',
            'dailyStats'
        ));
    }
}
