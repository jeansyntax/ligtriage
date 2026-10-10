<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntranceController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StaffQueueController;
use App\Http\Controllers\StaffTicketController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('staff.queue')
    : redirect()->route('login'));

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

# one-time setup for hosts without a terminal. needs SETUP_KEY, remove it after use
Route::get('/setup/{key}', function (string $key) {
    $real = (string) config('triage.setup_key');
    abort_if($real === '' || ! hash_equals($real, $key), 404);

    Artisan::call('migrate', ['--force' => true]);
    $log = Artisan::output();
    Artisan::call('db:seed', ['--force' => true]);
    $log .= Artisan::output();

    return response('<pre>'.e($log).'</pre><p>Done. Now delete SETUP_KEY in your host settings.</p>');
});

# patient side, no login
Route::get('/scan/{token}', [ScanController::class, 'scan'])->middleware('throttle:60,1')->name('scan');
Route::get('/t/{accessToken}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/t/{accessToken}/status', [TicketController::class, 'status'])->name('ticket.status');
Route::post('/t/{accessToken}/answers', [TicketController::class, 'submit'])->name('ticket.submit');
Route::post('/t/{accessToken}/cancel', [TicketController::class, 'cancel'])->name('ticket.cancel');

# staff side
Route::middleware('auth')->group(function () {
    Route::get('/entrance', [EntranceController::class, 'show'])->name('entrance.show');
    Route::get('/entrance/current', [EntranceController::class, 'current'])->name('entrance.current');

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/queue', [StaffQueueController::class, 'index'])->name('queue');
        Route::get('/queue/partial', [StaffQueueController::class, 'partial'])->name('queue.partial');
        Route::post('/queue/call-next', [StaffQueueController::class, 'callNext'])->name('queue.next');

        Route::get('/tickets/create', [StaffTicketController::class, 'create'])->name('create');
        Route::post('/tickets', [StaffTicketController::class, 'store'])->name('store');
        Route::get('/tickets/{ticket}', [StaffTicketController::class, 'show'])->name('ticket');
        Route::post('/tickets/{ticket}/priority', [StaffTicketController::class, 'priority'])->name('ticket.priority');
        Route::post('/tickets/{ticket}/status', [StaffTicketController::class, 'status'])->name('ticket.status');

        Route::get('/summary', [SummaryController::class, 'index'])->name('summary');

        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
