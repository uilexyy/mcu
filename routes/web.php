<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SPA catch-all for production (refreshing any Vue route)
Route::get('/{any}', function () {
    return file_get_contents(public_path('frontend/dist/index.html'));
})->where('any', '.*');
