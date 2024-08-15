<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use \App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('posts', PostController::class);
        Route::prefix('users')->group(function () {
            Route::post('/{user}/follow', [FollowController::class, 'follow']);
            Route::delete('/{user}/unfollow', [FollowController::class, 'unfollow']);
            Route::put('/{user}/accept', [FollowController::class, 'acceptFollowing']);
            Route::get('/following', [FollowController::class, 'getFollowing']);;
            Route::get('/{username}/followers', [FollowController::class, 'getFollower']);;
            Route::get('/', [UserController::class, 'getUsers']);;
            Route::get('/{username}', [UserController::class, 'getDetail']);;
        });
    });
});
