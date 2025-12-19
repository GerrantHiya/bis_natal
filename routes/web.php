<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusAssignmentController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes (Authenticated users)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Participants
    Route::resource('participants', ParticipantController::class)->except(['show']);
    Route::post('/participants/import', [ParticipantController::class, 'import'])->name('participants.import');
    Route::post('/participants/{participant}/toggle-priority', [ParticipantController::class, 'togglePriority'])->name('participants.toggle-priority');

    // Buses
    Route::get('/buses', [BusController::class, 'index'])->name('buses.index');
    Route::post('/buses', [BusController::class, 'store'])->name('buses.store');
    Route::get('/buses/{bus}', [BusController::class, 'show'])->name('buses.show');
    Route::put('/buses/{bus}', [BusController::class, 'update'])->name('buses.update');
    Route::delete('/buses/{bus}', [BusController::class, 'destroy'])->name('buses.destroy');
    Route::post('/buses/default-capacity', [BusController::class, 'updateDefaultCapacity'])->name('buses.default-capacity');

    // Assignments
    Route::get('/assignments', [BusAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/auto', [BusAssignmentController::class, 'autoAssign'])->name('assignments.auto');
    Route::post('/assignments/manual', [BusAssignmentController::class, 'manualAssign'])->name('assignments.manual');
    Route::delete('/assignments/{assignment}', [BusAssignmentController::class, 'removeAssignment'])->name('assignments.remove');
    Route::post('/assignments/reset', [BusAssignmentController::class, 'reset'])->name('assignments.reset');
    Route::get('/assignments/export', [BusAssignmentController::class, 'export'])->name('assignments.export');

    // Attendance (All users)
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{bus}', [\App\Http\Controllers\AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/toggle', [\App\Http\Controllers\AttendanceController::class, 'toggle'])->name('attendance.toggle');

    // Activity Logs (Admin only - checked in controller)
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    // User Management (Admin only - checked in controller)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

    // Password Change (All users)
    Route::get('/password/change', [UserController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [UserController::class, 'changePassword']);
});
