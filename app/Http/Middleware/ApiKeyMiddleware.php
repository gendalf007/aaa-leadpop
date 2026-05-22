<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API ключ не предоставлен'], 401);
        }
        
        $site = Site::where('api_key', $apiKey)->where('is_active', true)->first();
        
        if (!$site) {
            return response()->json(['error' => 'Неверный API ключ или сайт неактивен'], 401);
        }
        
        // Добавляем сайт в request для использования в контроллере
        $request->merge(['site' => $site]);
        
        return $next($request);
    }
}
