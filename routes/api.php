<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

//Route::post('register', 'App\Http\Controllers\API\RegisteredUserController@register');


Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('subscribe/{rubric_id}/user/{email}', ['uses' => 'Api\SubscribeController@subscribe', 'as' => 'subscribe'])
    ->middleware('auth')
    ->where(['rubric_id' => '[0-9]+']);
Route::delete('subscribe/{rubric_id}/user/{email}', ['uses' => 'Api\SubscribeController@deleteSubscribe', 'as' => 'delete.subscribe'])
    ->middleware('auth')
    ->where(['rubric_id' => '[0-9]+']);
Route::delete('subscriptions/user/{email}', ['uses' => 'Api\SubscribeController@deleteSubscribes', 'as' => 'delete.subscribes'])
    ->middleware('auth')
    ->where(['id' => '[0-9]+']);
Route::get('subscriptions/rubric/{rubric_id}', ['uses' => 'Api\SubscribeController@subscriptionsRubric', 'as' => 'subscriptions.rubric'])
    ->middleware('auth')
    ->where(['rubric_id' => '[0-9]+']);

