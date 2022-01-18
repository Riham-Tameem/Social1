<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PostController;
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
Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/forgetPassword',[\App\Http\Controllers\Auth\ForgotPasswordController::class,'sendResetLinkEmail']);


Route::middleware('auth:api')->group( function () {
    Route::post('/logout',[AuthController::class,'logout']);
    Route::post('/search',[AuthController::class,'search']);

    Route::post('/addFriend',[AuthController::class,'addFriend']);
    Route::post('/removefriend',[AuthController::class,'removefriend']);
    Route::get('/friendlist',[AuthController::class,'friendlist']);


    Route::get('/view',[\App\Http\Controllers\Api\HomeController::class,'view']);

    Route::resource('posts', PostController::class);
    Route::post('/favourite',[PostController::class,'favourite']);
    Route::post('/share',[PostController::class,'share']);

    Route::post('/comment',[CommentController::class,'comment']);
    Route::put('/editComment/{comments}',[CommentController::class,'editComment']);
    Route::delete('/deleteComment/{comments}',[CommentController::class,'deleteComment']);


    Route::post('/like',[HomeController::class,'like']);


    Route::post('/viewProfile',[AuthController::class,'viewProfile']);
    Route::get('/getUser',[AuthController::class,'getUser']);
    Route::get('/getAuthUser',[AuthController::class,'getAuthUser']);
    Route::post('/changePassword',[AuthController::class,'changePassword']);
    Route::post('/editUser',[AuthController::class,'editUser']);

});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

