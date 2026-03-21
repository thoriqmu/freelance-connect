<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for Vue Router to handle client-side routing
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api/?).*$')->name('app');
