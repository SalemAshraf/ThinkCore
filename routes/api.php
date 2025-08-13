<?php

use App\Http\Controllers\ApiControllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ApiControllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\ApiControllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\ApiControllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\ApiControllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\ApiControllers\Admin\Auth\PasswordController;
use App\Http\Controllers\ApiControllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\ApiControllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\ApiControllers\Admin\CourseCategoryController;
use App\Http\Controllers\ApiControllers\Admin\CourseLanguageController;
use App\Http\Controllers\ApiControllers\Admin\CourseLevelController;
use App\Http\Controllers\ApiControllers\Admin\CourseSubCategoryController;
use App\Http\Controllers\ApiControllers\Admin\DashboardController;
use App\Http\Controllers\ApiControllers\Admin\InstructorRequestController;
use Illuminate\Support\Facades\Route;

/**
 * ===============================
 * Authentication & Password Reset Routes (Public)
 * ===============================
 */
Route::post('/login', [AuthenticatedSessionController::class, 'login'])
    ->name('admin.login');

Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

/**
 * ===============================
 * Protected Routes (Requires admin_api authentication)
 * ===============================
 */
// Route::group(["middleware" => 'auth:sanctum', 'admin_api_guard'], function () {

//     /**
//      * 📧 Email Verification
//      */
//     Route::get('verify-email', EmailVerificationPromptController::class)
//         ->name('verification.notice');

//     Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
//         ->middleware(['signed', 'throttle:6,1'])
//         ->name('verification.verify');

//     Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//         ->middleware('throttle:6,1')
//         ->name('verification.send');

//     /**
//      * 🔑 Password Confirmation & Update
//      */
//     Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
//         ->name('password.confirm');

//     Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

//     Route::put('password', [PasswordController::class, 'update'])
//         ->name('password.update');

//     /**
//      * 🚪 Logout
//      */
//     Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
//         ->name('logout');

//     /**
//      * 📊 Dashboard
//      */
//     Route::get('dashboard', [DashboardController::class, 'index'])
//         ->name('dashboard');

//     /**
//      * 🧾 Instructor Requests
//      */
//     Route::get('document-download/{user}', [InstructorRequestController::class, 'download'])
//         ->name('document-download');
//     Route::resource('instructors-requests', InstructorRequestController::class);

//     /**
//      * 📚 Course Management
//      */
//     // Course Languages
//     Route::resource('course-languages', CourseLanguageController::class);

//     // Course Levels
//     Route::resource('course-levels', CourseLevelController::class);

//     // Course Categories
//     Route::get('/course-categories', [CourseCategoryController::class, 'index']);

//     // Course Sub-Categories
//     Route::prefix('course-categories/{course_category}/sub-categories')->group(function () {
//         Route::get('/', [CourseSubCategoryController::class, 'index'])->name('course-sub-categories.index');
//         Route::get('/create', [CourseSubCategoryController::class, 'create'])->name('course-sub-categories.create');
//         Route::post('/', [CourseSubCategoryController::class, 'store'])->name('course-sub-categories.store');
//         Route::get('/{course_sub_category}/edit', [CourseSubCategoryController::class, 'edit'])->name('course-sub-categories.edit');
//         Route::put('/{course_sub_category}', [CourseSubCategoryController::class, 'update'])->name('course-sub-categories.update');
//         Route::delete('/{course_sub_category}', [CourseSubCategoryController::class, 'destroy'])->name('course-sub-categories.destroy');
//     });
// });


Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('api.admin.login');
    Route::middleware('auth:api_admin')->group(function () {
        Route::get('/course-languages', [CourseLanguageController::class, 'index']);
        // Admin-specific routes (e.g., manage courses, users)
    });
});
