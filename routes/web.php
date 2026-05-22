<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\SiteAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\FormFieldController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\UserController;


// Аутентификация (только для админки)
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Авторизация на сайтах
Route::get('/site/login', [SiteAuthController::class, 'showLoginForm'])->name('site.login');
Route::post('/site/login', [SiteAuthController::class, 'login'])->name('site.login.post');
Route::post('/site/logout', [SiteAuthController::class, 'logout'])->name('site.logout');

// Защищенные маршруты сайтов (требуют авторизации)
Route::middleware('site.access')->group(function () {
    Route::get('/site/profile', [SiteAuthController::class, 'profile'])->name('site.profile');
});

// Админка (требует аутентификации и прав администратора)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Управление пользователями
    Route::resource('users', UserController::class);
    
    // Управление сайтами
    Route::resource('sites', SiteController::class);
    Route::post('sites/{site}/regenerate-api-key', [SiteController::class, 'regenerateApiKey'])
        ->name('sites.regenerate-api-key');
    
    // Управление полями форм
    Route::resource('sites.fields', FormFieldController::class);
    Route::post('sites/{site}/fields/update-order', [FormFieldController::class, 'updateOrder'])
        ->name('sites.fields.update-order');
    Route::post('sites/{site}/fields/{field}/toggle-active', [FormFieldController::class, 'toggleActive'])
        ->name('sites.fields.toggle-active');
    
    // Просмотр заявок
    Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
    Route::post('requests/bulk-send', [RequestController::class, 'bulkSend'])->name('requests.bulk-send');
    Route::get('requests/{request}', [RequestController::class, 'show'])->name('requests.show');
});

// API для получения конфигурации сайта
Route::get('/api/site/{domain}', [FormController::class, 'getSiteConfig'])->name('api.site.config');

// Обработка отправки форм
Route::post('/submit', [FormController::class, 'submitForm'])->name('form.submit');

// Обработка всех запросов через контроллер доменов
Route::get('/{any?}', [DomainController::class, 'handle'])
    ->where('any', '.*');