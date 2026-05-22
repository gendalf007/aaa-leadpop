<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;

class DomainController extends Controller
{
    /**
     * Обработать запрос по домену
     */
    public function handle(Request $request)
    {
        $host = $request->getHost();
        
        // Если это APP_URL - показываем админку
        if ($host === parse_url(config('app.url'), PHP_URL_HOST)) {
            if (auth()->check()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login');
        }
        
        // Ищем сайт по домену
        $site = Site::where('domain', $host)->where('is_active', true)->first();
        
        if (!$site) {
            abort(404, 'Сайт не найден');
        }
        
        // Проверяем авторизацию
        if (!Auth::check()) {
            return redirect()->route('site.login');
        }
        
        $user = Auth::user();
        
        // Проверяем доступ к сайту
        if (!$user->isAdmin() && !$user->hasAccessToSite($site->id)) {
            Auth::logout();
            return redirect()->route('site.login')->withErrors([
                'email' => 'У вас нет доступа к этому сайту.',
            ]);
        }
        
        // Если все проверки пройдены, показываем форму
        $fields = $site->activeFields()->get();
        return view('form', compact('site', 'fields'));
    }
}
