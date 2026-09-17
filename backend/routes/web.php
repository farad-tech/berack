<?php

use App\Http\Controllers\SdkController;
use App\Http\Controllers\TrackerEventController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/assets/fonts/vazirmatn.css', function () {
    $fontUrl = url('/assets/fonts/vazirmatn/Vazirmatn-VariableFont_wght.ttf');

    return response(<<<CSS
@font-face {
    font-family: 'Vazirmatn';
    src: url('{$fontUrl}') format('truetype');
    font-weight: 100 900;
    font-style: normal;
    font-display: swap;
}
CSS, 200, [
        'Content-Type' => 'text/css; charset=UTF-8',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->name('assets.fonts.vazirmatn.css');

Route::get('/assets/fonts/vazirmatn/{file}', function (string $file) {
    abort_unless($file === 'Vazirmatn-VariableFont_wght.ttf', 404);

    $path = storage_path("app/private/Vazirmatn/{$file}");

    abort_unless(File::exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'font/ttf',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('file', '[A-Za-z0-9._-]+')->name('assets.fonts.vazirmatn.file');

Route::view('/', 'welcome');
Route::view('/guide', 'guide');
Route::permanentRedirect('/en', '/');
Route::permanentRedirect('/en/guide', '/guide');

Route::post('/save-tracker', [TrackerEventController::class, 'store']);
Route::options('/save-tracker', [TrackerEventController::class, 'preflight']);
Route::get('/sdk/{apiKey}.js', [SdkController::class, 'show']);

require __DIR__.'/panel.php';
