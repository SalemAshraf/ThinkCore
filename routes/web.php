<?php

use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\InstructorDashboardController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\UserDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ----------------------
 * Frontend Routes
 * ----------------------
 */
Route::get('/', [FrontendController::class, 'index'])->name('home');

/**
 * ----------------------
 * Student Routes
 * ----------------------
 */
Route::group(['middleware' => ['auth:web', 'verified', 'check_role:student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/become-instructor', [UserDashboardController::class, 'becomeInstructor'])->name('become-instructor');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/social', [ProfileController::class, 'updateSocial'])->name('profile.social.update');
});

/**
 * ----------------------
 * Instructor Routes
 * ----------------------
 */
Route::group(['middleware' => ['auth:web', 'verified', 'check_role:instructor'], 'prefix' => 'instructor', 'as' => 'instructor.'], function () {
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'instructorIndex'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/social', [ProfileController::class, 'updateSocial'])->name('profile.social.update');
    // Course Routes
    Route::get('/courses', [CourseController::class, 'index'])->name('courses');
    Route::get('/courses/create', [CourseController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses/store', [CourseController::class, 'storeBasicInfo'])->name('courses.store.basic_info');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::post('/courses/moreinfo', [CourseController::class, 'moreinfo'])->name('courses.moreinfo');

    // Public file manager disabled until media ownership and private delivery are implemented.

});

// Shared authentication callbacks need a stable, role-aware destination.
Route::get('/dashboard', function (Request $request) {
    return redirect()->route($request->user()->role === 'instructor' ? 'instructor.dashboard' : 'student.dashboard');
})->middleware(['auth:web', 'verified'])->name('dashboard');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
