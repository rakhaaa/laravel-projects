<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function() {
    Route::resource('expenses', ExpenseController::class)->withoutMiddleware(VerifyCsrfToken::class);
});
