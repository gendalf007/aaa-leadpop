<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Показать форму входа в админку
     */
    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }
    
    /**
     * Обработка входа в админку
     */
    public function login(Request $request)
    {
        $credentials = $request->only('login', 'password');
        
        // Находим пользователя по username или email
        $user = User::findByUsernameOrEmail($credentials['login']);
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Проверяем, что пользователь является администратором
            if ($user->isAdmin()) {
                Auth::login($user);
                $request->session()->regenerate();
                
                return redirect()->intended('/admin');
            } else {
                return back()->withErrors([
                    'login' => 'У вас нет прав администратора.',
                ]);
            }
        }
        
        return back()->withErrors([
            'login' => 'Неверные учетные данные.',
        ]);
    }
    
    /**
     * Выход из админки
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
