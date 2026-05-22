<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Added this import
use Illuminate\Support\Facades\Hash; // Added this import

class SiteAuthController extends Controller
{
    /**
     * Показать форму входа для сайта
     */
    public function showLoginForm(Request $request)
    {
        $host = $request->getHost();
        $site = Site::where('domain', $host)->where('is_active', true)->first();
        
        if (!$site) {
            abort(404, 'Сайт не найден');
        }
        
        return view('site.auth.login', compact('site'));
    }
    
    /**
     * Обработка входа
     */
    public function login(Request $request)
    {
        $host = $request->getHost();
        $site = Site::where('domain', $host)->where('is_active', true)->first();
        
        if (!$site) {
            abort(404, 'Сайт не найден');
        }
        
        $credentials = $request->only('login', 'password');
        
        // Находим пользователя по username или email
        $user = User::findByUsernameOrEmail($credentials['login']);
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Проверяем доступ к сайту
            if ($user->isAdmin() || $user->hasAccessToSite($site->id)) {
                Auth::login($user);
                $request->session()->regenerate();
                
                return redirect()->intended('/');
            } else {
                return back()->withErrors([
                    'login' => 'У вас нет доступа к этому сайту.',
                ]);
            }
        }
        
        return back()->withErrors([
            'login' => 'Неверные учетные данные.',
        ]);
    }
    
    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    /**
     * Показать профиль пользователя на сайте
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        $accessibleSites = $user->getAccessibleSites();

        // Для админа SiteAccessMiddleware не кладёт current_site в request —
        // берём первый доступный сайт как контекст.
        $site = $request->get('current_site') ?? $accessibleSites->first();

        return view('site.auth.profile', compact('user', 'site', 'accessibleSites'));
    }
}
