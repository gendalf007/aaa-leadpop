<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('sites')->orderBy('name')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.users.create', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'sites' => 'array',
            'sites.*' => 'exists:sites,id'
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $validated['is_admin'] ?? false,
        ]);
        
        // Привязываем сайты
        if (isset($validated['sites'])) {
            $user->sites()->attach($validated['sites']);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('sites');
        $accessibleSites = $user->getAccessibleSites();
        
        return view('admin.users.show', compact('user', 'accessibleSites'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();
        $user->load('sites');
        
        return view('admin.users.edit', compact('user', 'sites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'sites' => 'array',
            'sites.*' => 'exists:sites,id'
        ]);
        
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'is_admin' => $validated['is_admin'] ?? false,
        ]);
        
        // Обновляем пароль если указан
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }
        
        // Обновляем привязку к сайтам
        $user->sites()->sync($validated['sites'] ?? []);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Нельзя удалить самого себя
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нельзя удалить свой аккаунт');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален');
    }
}
