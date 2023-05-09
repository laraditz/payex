<?php

use Illuminate\Support\Facades\Route;
use Laraditz\Payex\Http\Controllers\PayexController;

Route::match(['get', 'post'], '/done', [PayexController::class, 'done'])->name('payex.done');
Route::post('/callback', [PayexController::class, 'callback'])->name('payex.callback');
