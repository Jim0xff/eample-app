<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/{id}', [TestController::class, 'test1']);
Route::get('/test2', [TestController::class, 'test2']);
Route::post('/test3', [TestController::class, 'test3']);
Route::get('/test4', [TestController::class, 'test4']);


Route::post('/uploadImg.json', ['uses' => '\App\Http\Controllers\ImageController@uploadImg']);

/**  Route::post('/user/create.json', [UserController::class, 'createUser'])->middleware([\App\Http\Middleware\BusinessThrottleRequestsMiddleware::class.':60,60,true']); */
Route::post('/user/create.json', [UserController::class, 'createUser']);

Route::post('/user/edit.json', ['uses' => '\App\Http\Controllers\UserController@editUser'])->middleware([\App\Http\Middleware\ApiToken::class]);
Route::get('/user/querySingle.json', [UserController::class, 'getUser']);
Route::post('/user/followUser.json', ['uses' => '\App\Http\Controllers\UserController@followUser','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::post('/user/cancelFollowUser.json', ['uses' => '\App\Http\Controllers\UserController@cancelFollowUser','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/user/followerList.json', ['uses' => '\App\Http\Controllers\UserController@followerList']);
Route::get('/user/followingList.json', ['uses' => '\App\Http\Controllers\UserController@followingList']);



Route::post('/token/create.json', ['uses' => '\App\Http\Controllers\TokenController@createToken','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/token/tokenDetail.json', ['uses' => '\App\Http\Controllers\TokenController@tokenDetail']);
Route::post('/token/tokenList.json', ['uses' => '\App\Http\Controllers\TokenController@tokenList']);
Route::get('/token/tokenHolders.json', ['uses' => '\App\Http\Controllers\TokenController@tokenHolder']);
Route::get('/token/tradingList.json', ['uses' => '\App\Http\Controllers\TokenController@tradingList']);
Route::post('/token/boughtTokenList.json', ['uses' => '\App\Http\Controllers\TokenController@userBoughtTokens']);
Route::get('/token/history.json', ['uses' => '\App\Http\Controllers\TokenController@getHistory']);
Route::get('/token/time.json', ['uses' => '\App\Http\Controllers\TokenController@getTime']);
Route::get('/token/config.json', ['uses' => '\App\Http\Controllers\TokenController@getConfig']);


Route::post('/comment/create.json', ['uses' => '\App\Http\Controllers\CommentController@createComment','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::post('/comment/pageSearchComment.json', ['uses' => '\App\Http\Controllers\CommentController@pageSearchComment']);
Route::get('/comment/userComment.json', ['uses' => '\App\Http\Controllers\CommentController@userComments']);

Route::post('/comment/clickLike.json',  ['uses' => '\App\Http\Controllers\CommentController@clickLike'])->middleware([\App\Http\Middleware\ApiToken::class]);
Route::get('/comment/userLikeList.json', ['uses' => '\App\Http\Controllers\CommentController@clickLikeList','middleware' => \App\Http\Middleware\ApiToken::class]);








