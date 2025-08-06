<?php

use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\InstructorDashboardController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\UserDashboardController;
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
    Route::get('/dashboard',[UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/become-instructor',[UserDashboardController::class, 'becomeInstructor'])->name('become-instructor');

    // Profile Routes
    Route::get('/profile',[ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update',[ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/social',[ProfileController::class, 'updateSocial'])->name('profile.social.update');

});

/**
 * ----------------------
 * Instructor Routes
 * ----------------------
*/
Route::group(['middleware' => ['auth:web', 'verified', 'check_role:instructor'], 'prefix' => 'instructor', 'as' => 'instructor.'], function () {
    Route::get('/dashboard',[InstructorDashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile',[ProfileController::class, 'instructorIndex'])->name('profile');
    Route::post('/profile/update',[ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/social',[ProfileController::class, 'updateSocial'])->name('profile.social.update');
});






require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
