<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('user', UserController::class)->middleware('auth:sanctum');

Route::apiResource('recipe', RecipeController::class);
Route::apiResource('like', LikeController::class)->middleware('auth:sanctum');
Route::apiResource('bookmark', BookmarkController::class)->middleware('auth:sanctum');
Route::apiResource('comment', CommentController::class)->middleware('auth:sanctum');

Route::get('/user-recipe', [HomeController::class, 'userRecipe'])->middleware('auth:sanctum');
Route::get('/best-recipe', [HomeController::class, 'bestRecipe']);
Route::get('/search', [HomeController::class, 'search']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
