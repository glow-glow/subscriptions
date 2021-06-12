<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::post('register', 'App\Http\Controllers\API\RegisteredUserController@register');


Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');
