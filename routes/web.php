<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CredentialsController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\DashboardsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $pages = \App\Models\Page::where('is_published', true)->get();

    // Filter pages visible to the current user
    if (auth()->check()) {
        $pages = $pages->filter(function (\App\Models\Page $page) {
            return auth()->user()->hasAccessToPage($page, 'can_view');
        });
    } else {
        $pages = collect();
    }

    return view('welcome', compact('pages'));
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('index');

    // Admin-only Management
    Route::middleware(['admin'])->group(function () {
        // User Management
        Route::get('/users', [PanelController::class, 'users'])->name('users');
        Route::get('/users/create', [PanelController::class, 'createUser'])->name('users.create');
        Route::post('/users', [PanelController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [PanelController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [PanelController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [PanelController::class, 'deleteUser'])->name('users.delete');

        // Permission Management
        Route::get('/users/{user}/permissions', [PanelController::class, 'permissions'])->name('users.permissions');
        Route::post('/users/{user}/permissions', [PanelController::class, 'updatePermissions'])->name('users.permissions.update');

        // Credentials Management
        Route::resource('credentials', CredentialsController::class);

        // Group & Category Management
        Route::resource('groups', GroupsController::class);
        Route::resource('categories', CategoriesController::class);
    });

    // Editor / Staff Routes
    Route::middleware(['staff'])->group(function () {
        Route::post('/pages/test', [PagesController::class, 'testUpstream'])->name('pages.test');
        Route::resource('forms', FormsController::class);
        Route::resource('dashboards', DashboardsController::class);
    });
});

// Profile Routes (any authenticated user)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Public Assets (for Welcome/Login)
Route::prefix('assets')->name('assets.')->group(function () {
    Route::get('/panel.css', [AssetController::class, 'adminCss'])->name('panel.css');
    Route::get('/theme/{theme}.css', [AssetController::class, 'themeCss'])->name('theme.css');
    Route::get('/panel.js', [AssetController::class, 'adminJs'])->name('panel.js');
});

Route::middleware(['auth'])->prefix('front')->group(function () {
    Route::get('/{slug}', [FrontController::class, 'show'])->name('front.show');
    Route::post('/{slug}/action', [FrontController::class, 'proxy'])->name('front.proxy');
    Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');
});
