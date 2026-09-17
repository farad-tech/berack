<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\SiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('panel')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('panel.login');
        Route::get('/register', [AuthController::class, 'registerForm'])->name('panel.register');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
        Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
    });

    Route::middleware('auth')->name('panel.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [SiteController::class, 'dashboard'])->name('home');
        Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
        Route::get('/installation', [SiteController::class, 'installation'])->name('installation');
        Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
        Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
        Route::get('/sites/{site}/reports', [SiteController::class, 'reports'])->name('sites.reports');
        Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
        Route::get('/sites/{site}', [SiteController::class, 'edit'])->name('sites.show');
        Route::put('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
        Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
    });
});
