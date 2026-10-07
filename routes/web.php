<?php

use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Identity\InstructorProfileController;
use App\Http\Controllers\Identity\RegistrationController;
use App\Http\Controllers\Identity\SessionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegistrationController::class, 'create'])->name('register');
    Route::post('register', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});
Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('dashboard')->middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('instructor', [InstructorProfileController::class, 'create'])->name('instructor.apply');
    Route::post('instructor', [InstructorProfileController::class, 'store'])->middleware('throttle:5,1')->name('instructor.store');
    Route::resource('courses', CourseController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::prefix('admin')->middleware('can:admin')->group(function (): void {
        Route::get('instructors', [InstructorProfileController::class, 'index'])->name('instructors.index');
        Route::post('instructors/{instructorProfile}/approve', [InstructorProfileController::class, 'approve'])->name('instructors.approve');
        Route::post('instructors/{instructorProfile}/reject', [InstructorProfileController::class, 'reject'])->name('instructors.reject');
        Route::resource('categories', CategoryController::class)->only(['index', 'store']);
    });
});
