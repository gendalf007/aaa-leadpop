<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormRequest;
use App\Services\PlexCrm\PlexLeadDispatcher;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FormRequest::with(['site', 'user']);
        
        // Фильтрация по сайту
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        
        // Фильтрация по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Фильтрация по дате
        if ($request->filled('date_from')) {
            if ($request->filled('time_from')) {
                $query->where('created_at', '>=', $request->date_from . ' ' . $request->time_from . ':00');
            } else {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
        }

        if ($request->filled('date_to')) {
            if ($request->filled('time_to')) {
                $query->where('created_at', '<=', $request->date_to . ' ' . $request->time_to . ':59');
            } else {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        // Фильтрация по времени суток (без даты)
        if (!$request->filled('date_from') && $request->filled('time_from')) {
            $query->whereTime('created_at', '>=', $request->time_from);
        }

        if (!$request->filled('date_to') && $request->filled('time_to')) {
            $query->whereTime('created_at', '<=', $request->time_to);
        }
        
        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(form_data, '$.name') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(form_data, '$.phone') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(form_data, '$.email') LIKE ?", ["%{$search}%"]);
            });
        }
        
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [20, 50, 100, 200, 500])) {
            $perPage = 20;
        }

        $requests = $query->latest()->paginate($perPage)->appends($request->query());
        
        return view('admin.requests.index', compact('requests'));
    }

    /**
     * Массовая повторная отправка заявок в CRM
     */
    public function bulkSend(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Не выбрано ни одной заявки');
        }

        $requests = FormRequest::with('site')->whereIn('id', $ids)->get();
        $dispatcher = app(PlexLeadDispatcher::class);
        $queued = 0;
        $skipped = 0;

        foreach ($requests as $r) {
            $before = $r->crm_status;
            try {
                $dispatcher->dispatch($r);
            } catch (\Throwable $e) {
                \Log::error('Lead dispatch failed (bulk)', [
                    'form_request_id' => $r->id,
                    'error'           => $e->getMessage(),
                ]);
            }
            $r->refresh();
            if ($r->crm_status === 'skipped' && $before !== 'sent') {
                $skipped++;
            } else {
                $queued++;
            }
        }

        $msg = "Отправлено в очередь: {$queued}";
        if ($skipped > 0) {
            $msg .= ". Пропущено (Plex CRM не сконфигурирован): {$skipped}";
        }

        return back()->with('success', $msg);
    }

    /**
     * Display the specified resource.
     */
    public function show(FormRequest $request)
    {
        $request->load(['site', 'user']);
        return view('admin.requests.show', compact('request'));
    }
}
