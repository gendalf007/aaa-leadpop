<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class SiteAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        // Администраторы имеют доступ ко всем сайтам
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Получаем домен из запроса
        $host = $request->getHost();
        $site = Site::where('domain', $host)->where('is_active', true)->first();
        
        if (!$site) {
            abort(404, 'Сайт не найден');
        }
        
        // Проверяем, есть ли у пользователя доступ к этому сайту
        if (!$user->hasAccessToSite($site->id)) {
            abort(403, 'У вас нет доступа к этому сайту');
        }
        
        // Добавляем сайт в request для использования в контроллерах
        $request->merge(['current_site' => $site]);
        
        return $next($request);
    }
}
