<?php

use App\Http\Controllers\Api\PushSubscriptionController;
use App\Livewire\Admin\Objectives;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DayThemes;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\PeopleMet;
use App\Livewire\Admin\Routines;
use App\Livewire\Admin\Tasks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Serve the Firebase SW with config baked in
Route::get('/firebase-messaging-sw.js', function () {
    $config = config('services.firebase');
    $js = "self.__firebaseConfig = " . json_encode([
        'apiKey'            => $config['api_key'],
        'authDomain'        => $config['auth_domain'],
        'projectId'         => $config['project_id'],
        'storageBucket'     => $config['storage_bucket'],
        'messagingSenderId' => $config['messaging_sender_id'],
        'appId'             => $config['app_id'],
    ]) . ";\n";
    $js .= file_get_contents(public_path('firebase-messaging-sw.js'));
    return response($js, 200)->header('Content-Type', 'application/javascript');
})->name('firebase.sw');

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

        // Quarterly Objectives
        Route::get('objectives', Objectives\Index::class)->name('admin.objectives.index');
        Route::get('objectives/create', Objectives\Form::class)->name('admin.objectives.create');
        Route::get('objectives/{id}/edit', Objectives\Form::class)->name('admin.objectives.edit');

        // People Met
        Route::get('people-met', PeopleMet\Index::class)->name('admin.people-met.index');
        Route::get('people-met/create', PeopleMet\Form::class)->name('admin.people-met.create');
        Route::get('people-met/{id}/edit', PeopleMet\Form::class)->name('admin.people-met.edit');

        // Native Web Push subscriptions
        Route::post('push-subscription', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
        Route::delete('push-subscription', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

        Route::post('logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('admin.logout');
    });
});
