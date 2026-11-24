<?php

use App\Http\Controllers\Admin\CourseReviewController;
use App\Http\Controllers\Admin\LearnerReviewController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\CurriculumController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\InstructorDashboardController;
use App\Http\Controllers\Frontend\LearningController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\UserDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ----------------------
 * Frontend Routes
 * ----------------------
 */
Route::get('/', [LearningController::class, 'catalog'])->name('home');

/**
 * ----------------------
 * Student Routes
 * ----------------------
 */
Route::group(['middleware' => ['auth:web', 'verified', 'check_role:student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/become-instructor', [UserDashboardController::class, 'becomeInstructor'])->name('become-instructor');
    Route::post('/become-instructor', [UserDashboardController::class, 'applyInstructor'])->middleware('throttle:5,1')->name('become-instructor.apply');

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

Route::get('/courses', [LearningController::class, 'catalog'])->name('marketplace.catalog');
Route::get('/courses/{slug}', [LearningController::class, 'show'])->name('marketplace.course');
Route::get('/courses/{slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('marketplace.lesson');
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::post('/courses/{slug}/enroll', [LearningController::class, 'enroll'])->name('marketplace.enroll');
    Route::post('/courses/{slug}/lessons/{lesson}/complete', [LearningController::class, 'complete'])->name('marketplace.complete');
    Route::get('/my-learning', [LearningController::class, 'library'])->name('marketplace.library');
});
Route::middleware(['auth:web', 'verified', 'check_role:instructor'])->prefix('instructor/courses/{course}')->group(function () {
    Route::get('/curriculum', [CurriculumController::class, 'index'])->name('instructor.curriculum');
    Route::patch('/lessons/{lesson}', [CurriculumController::class, 'update'])->name('instructor.lessons.update');
    Route::delete('/lessons/{lesson}', [CurriculumController::class, 'destroy'])->name('instructor.lessons.destroy');
    Route::post('/lessons', [CurriculumController::class, 'store'])->name('instructor.lessons.store');
    Route::post('/submit', [CurriculumController::class, 'submit'])->name('instructor.course.submit');
});
Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/course-reviews', [CourseReviewController::class, 'index'])->name('admin.course-reviews');
    Route::post('/course-reviews/{course}', [CourseReviewController::class, 'update'])->name('admin.course-reviews.update');
});

Route::post('/courses/{slug}/reviews', [App\Http\Controllers\Frontend\CourseReviewController::class, 'store'])->middleware(['auth:web', 'verified', 'throttle:10,1'])->name('marketplace.reviews.store');
Route::middleware(['auth:web', 'verified', 'check_role:instructor'])->prefix('instructor/courses/{course}')->group(function () {
    Route::post('/sections', [CurriculumController::class, 'storeSection'])->name('instructor.sections.store');
    Route::patch('/sections/{section}', [CurriculumController::class, 'updateSection'])->name('instructor.sections.update');
    Route::delete('/sections/{section}', [CurriculumController::class, 'destroySection'])->name('instructor.sections.destroy');
});
Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/learner-reviews', [LearnerReviewController::class, 'index'])->name('admin.learner-reviews');
    Route::post('/learner-reviews/{review}', [LearnerReviewController::class, 'update'])->name('admin.learner-reviews.update');
});
