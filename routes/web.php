<?php

use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\SalesforceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Password setup routes (token-based)
Route::get('/password/setup/{token}', [PasswordSetupController::class, 'show'])
    ->name('password.setup');
Route::post('/password/setup/{token}', [PasswordSetupController::class, 'store'])
    ->name('password.setup.store');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects - Rate limited on create/update to prevent abuse
    Route::resource('projects', ProjectController::class)->except(['store', 'update']);
    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('throttle:10,1') // 10 créations par minute max
        ->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])
        ->middleware('throttle:20,1') // 20 modifications par minute max
        ->name('projects.update');

    // Project duplication - Pre-fill create form with project data
    Route::get('/projects/{project}/duplicate', [ProjectController::class, 'duplicate'])
        ->name('projects.duplicate');

    // Project documents - Download with authentication
    Route::get('/projects/{project}/documents/{document}/download', [ProjectController::class, 'downloadDocument'])
        ->name('projects.documents.download');

    // Project documents - Delete specific document
    Route::delete('/projects/{project}/documents/{document}', [ProjectController::class, 'deleteDocument'])
        ->name('projects.documents.delete');

    // Project report - Redirect to Microsoft Planner report with secure token
    Route::get('/projects/{project}/report', [ProjectController::class, 'report'])
        ->name('projects.report');

    Route::get('/projects/{project}/recouvrement', [ProjectController::class, 'recouvrement'])
        ->middleware('throttle:60,1')
        ->name('projects.recouvrement');

    // API: Retrieve cached duplication data for large projects
    Route::get('/api/duplicate-data/{cacheKey}', [ProjectController::class, 'getDuplicateData'])
        ->name('duplicate.data');

    // Wizard step validation (AJAX)
    Route::post('/projects/wizard/validate/{step}', [ProjectController::class, 'wizardValidateStep'])
        ->where('step', '[1-3]')
        ->name('projects.wizard.validate');
    Route::post('/projects/{project}/wizard/validate/{step}', [ProjectController::class, 'wizardValidateStepEdit'])
        ->where('step', '[1-3]')
        ->name('projects.wizard.validate.edit');

    // Salesforce API (AJAX) - Rate limited to prevent API quota exhaustion
    Route::get('/api/salesforce/opportunities', [SalesforceController::class, 'searchOpportunities'])
        ->middleware('throttle:60,1') // 60 requests per minute
        ->name('salesforce.search');

    // Admin routes (require admin role)
    Route::middleware('role:Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('activity.export');
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [AdminController::class, 'toggle'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.resetPassword');
        Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    });
});
