<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;

Route::get('/', [PagesController::class, 'main'])
    ->name('main');

Route::post('/', [PagesController::class, 'action'])
    ->name('action');
