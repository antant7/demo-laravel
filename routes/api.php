<?php

use App\Http\Controllers\Api\UrlController;
use Illuminate\Support\Facades\Route;

Route::post('/shorten', [UrlController::class, 'shortenUrl']);
Route::get('/urls/{id}/stats', [UrlController::class, 'getUrlStats']);
