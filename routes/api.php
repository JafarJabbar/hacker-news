<?php

use App\Http\Controllers\Comments\CommentController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Users\AuthController;
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

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
    Route::get('/', [PostController::class, 'getPosts'])->name('list');
    Route::get('/{id}', [PostController::class, 'getPostByID'])->name('show');
});


/**
 * Cache flush
 */
Route::get('/cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return response()->json([
        'data' => "Cache cleared"
    ]);
})->name('cache');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'account'], function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
        Route::post('/', [PostController::class, 'createPost'])->name('create');
        Route::put('/{id}', [PostController::class, 'updatePost'])->name('update');
        Route::delete('/{id}', [PostController::class, 'deletePost'])->name('delete');
        Route::put('upvote/{id}', [PostController::class, 'upvotePost'])->name('upvote');
        Route::group(['prefix' => 'comments', 'as' => 'comments.'], function () {
            Route::post('/{post_id}', [CommentController::class, 'createComment'])->name('create');
            Route::delete('/{id}', [CommentController::class, 'deleteComment'])->name('delete');
        });
    });
});
