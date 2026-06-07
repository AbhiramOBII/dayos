<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DayThemes;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Routines;
use App\Livewire\Admin\Tasks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('login', Login::class)->name('admin.login');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', Dashboard::class)->name('admin.dashboard');
        Route::get('today', \App\Livewire\Admin\Today::class)->name('admin.today');
        Route::get('upskilling', \App\Livewire\Admin\Upskilling::class)->name('admin.upskilling');
        Route::get('day-tracker', \App\Livewire\Admin\DayTracker::class)->name('admin.day-tracker');
        Route::get('insights', \App\Livewire\Admin\Insights::class)->name('admin.insights');

        // Day Themes
        Route::get('day-themes', DayThemes\Index::class)->name('admin.day-themes.index');
        Route::get('day-themes/create', DayThemes\Form::class)->name('admin.day-themes.create');
        Route::get('day-themes/{id}/edit', DayThemes\Form::class)->name('admin.day-themes.edit');

        // Tasks
        Route::get('tasks', Tasks\Index::class)->name('admin.tasks.index');
        Route::get('tasks/create', Tasks\Form::class)->name('admin.tasks.create');
        Route::get('tasks/dump', Tasks\Dump::class)->name('admin.tasks.dump');
        Route::get('tasks/{id}/edit', Tasks\Form::class)->name('admin.tasks.edit');

        // Routines
        Route::get('routines', Routines\Index::class)->name('admin.routines.index');
        Route::get('routines/create', Routines\Form::class)->name('admin.routines.create');
        Route::get('routines/{id}/edit', Routines\Form::class)->name('admin.routines.edit');

        Route::post('logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('admin.logout');
    });
});
