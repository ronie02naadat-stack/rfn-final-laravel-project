<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->user_type === 'teacher') {
            return redirect()->route('teacher.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }
    return redirect('/login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/password/verify', [AuthController::class, 'verifyPassword'])->name('password.verify');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');

    // Password change routes for first-time login
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change.show');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');

    // Dashboard routes with role-based access
    Route::middleware('check-user-type:student')->get('/student-dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::middleware('check-user-type:teacher')->get('/teacher-dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    
    Route::middleware('check-user-type:admin')->group(function () {
        Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/latest-counts', [AdminDashboardController::class, 'getLatestCounts'])->name('admin.latest-counts');
        Route::get('/admin/user/{id}', [AdminDashboardController::class, 'getUserData'])->name('admin.user-data');
        Route::get('/admin/create', [AdminDashboardController::class, 'create'])->name('admin.create');
        Route::post('/admin/store', [AdminDashboardController::class, 'store'])->name('admin.store');
        Route::get('/admin/edit/{id}', [AdminDashboardController::class, 'edit'])->name('admin.edit');
        Route::put('/admin/update/{id}', [AdminDashboardController::class, 'update'])->name('admin.update');
        Route::delete('/admin/users/{id}', [AdminDashboardController::class, 'destroy'])->name('admin.destroy');
    });
});

// Student routes - with maintenance mode check (only index/show - management via admin dashboard)
Route::middleware('check-maintenance-mode')->group(function () {
    Route::resource('students', StudentController::class, ['only' => ['index', 'show']]);
});

// Other resource routes - without maintenance redirect (show promotion instead)
Route::resource('degrees', DegreeController::class);
Route::resource('profiles', ProfileController::class);
Route::resource('posts', PostController::class);
Route::resource('courses', CourseController::class);

// Custom course routes
Route::get('/courses-by-degree', [CourseController::class, 'byDegree'])->name('courses.by-degree');

// Log routes - without maintenance redirect
Route::prefix('logs')->name('logs.')->controller(LogController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/clear', 'clear')->name('clear');
    Route::get('/download', 'download')->name('download');
});

// Maintenance mode routes
Route::get('/maintenance-mode', [MaintenanceController::class, 'show'])->name('maintenance.show');

Route::prefix('maintenance')->name('maintenance.')->middleware('auth')->controller(MaintenanceController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::post('/{id}/activate', 'activate')->name('activate');
    Route::post('/{id}/deactivate', 'deactivate')->name('deactivate');
    Route::delete('/{id}', 'destroy')->name('destroy');
});
