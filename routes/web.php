<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RedirectController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/{shortCode}', [RedirectController::class, 'redirectToUrl'])->name('redirect_url');

