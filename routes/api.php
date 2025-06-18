<?php
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->post('/logout',[UserController::class,'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('books', BookController::class)->only(['store', 'update', 'destroy']);
});
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
