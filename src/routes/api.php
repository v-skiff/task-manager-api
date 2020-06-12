<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');

Route::middleware('auth:api')->group(function() {
    Route::namespace('Api')->group(function() {
        Route::apiResource('users', 'UserController');
        Route::patch('tasks/change_user', 'TaskController@changeUser');
        Route::patch('tasks/change_status', 'TaskController@changeStatus');
        Route::apiResource('tasks', 'TaskController');
    });
    Route::fallback(function () {
        return response()->json(['message' => 'Route Not Found'], 404);
    })->name('api.fallback.404');
});
