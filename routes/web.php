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
Route::get('/test88', [TestController::class, 'test88']);


Route::post('/uploadImg.json', ['uses' => '\App\Http\Controllers\ImageController@uploadImg']);
Route::post('/uploadFile.json', ['uses' => '\App\Http\Controllers\ImageController@uploadFile']);

/**  Route::post('/user/create.json', [UserController::class, 'createUser'])->middleware([\App\Http\Middleware\BusinessThrottleRequestsMiddleware::class.':60,60,true']); */
Route::post('/user/create.json', [UserController::class, 'createUser']);

Route::post('/user/edit.json', ['uses' => '\App\Http\Controllers\UserController@editUser'])->middleware([\App\Http\Middleware\ApiToken::class]);
Route::get('/user/querySingle.json', [UserController::class, 'getUser']);
Route::post('/user/followUser.json', ['uses' => '\App\Http\Controllers\UserController@followUser','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::post('/user/cancelFollowUser.json', ['uses' => '\App\Http\Controllers\UserController@cancelFollowUser','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/user/followerList.json', ['uses' => '\App\Http\Controllers\UserController@followerList']);
Route::get('/user/followingList.json', ['uses' => '\App\Http\Controllers\UserController@followingList']);


Route::get('/graphiql', [\MLL\GraphiQL\GraphiQLController::class, '__invoke']);
Route::post('/token/create.json', ['uses' => '\App\Http\Controllers\TokenController@createToken','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/token/tokenDetail.json', ['uses' => '\App\Http\Controllers\TokenController@tokenDetail']);
Route::get('/token/topOfTheMoon.json', ['uses' => '\App\Http\Controllers\TokenController@topOfTheMoon']);

Route::post('/token/tokenList.json', ['uses' => '\App\Http\Controllers\TokenController@tokenList']);
Route::get('/token/tokenHolders.json', ['uses' => '\App\Http\Controllers\TokenController@tokenHolder']);
Route::get('/token/tradingList.json', ['uses' => '\App\Http\Controllers\TokenController@tradingList']);
Route::post('/token/boughtTokenList.json', ['uses' => '\App\Http\Controllers\TokenController@userBoughtTokens']);
Route::get('/token/history.json', ['uses' => '\App\Http\Controllers\TokenController@getHistory']);
Route::get('/token/historyMock.json', ['uses' => '\App\Http\Controllers\TokenController@getHistoryMock']);
Route::get('/time.json', ['uses' => '\App\Http\Controllers\TokenController@getTime']);
Route::get('/token/config.json', ['uses' => '\App\Http\Controllers\TokenController@getConfig']);
Route::get('/token/getTokenTradingAmount.json', ['uses' => '\App\Http\Controllers\TokenController@getTokenTradingAmount']);


Route::get('/history', ['uses' => '\App\Http\Controllers\TokenController@getHistoryPure']);
Route::get('/time', ['uses' => '\App\Http\Controllers\TokenController@getTimePure']);
Route::get('/config', ['uses' => '\App\Http\Controllers\TokenController@getConfigPure']);
Route::get('/symbols', ['uses' => '\App\Http\Controllers\TokenController@resolveSymbol']);
Route::get('/searchSymbols', ['uses' => '\App\Http\Controllers\TokenController@searchSymbols']);




Route::post('/comment/create.json', ['uses' => '\App\Http\Controllers\CommentController@createComment','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::post('/comment/pageSearchComment.json', ['uses' => '\App\Http\Controllers\CommentController@pageSearchComment']);
Route::get('/comment/userComment.json', ['uses' => '\App\Http\Controllers\CommentController@userComments']);

Route::post('/comment/clickLike.json',  ['uses' => '\App\Http\Controllers\CommentController@clickLike'])->middleware([\App\Http\Middleware\ApiToken::class]);
Route::get('/comment/userLikeList.json', ['uses' => '\App\Http\Controllers\CommentController@clickLikeList','middleware' => \App\Http\Middleware\ApiToken::class]);


Route::post('/coBuildAgent/sendChat.json', ['uses' => '\App\Http\Controllers\CoBuildAgentController@sendChat','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/coBuildAgent/chatList.json', ['uses' => '\App\Http\Controllers\CoBuildAgentController@chatList','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::get('/coBuildAgent/myContributeData.json', ['uses' => '\App\Http\Controllers\CoBuildAgentController@myContributeData','middleware' => \App\Http\Middleware\ApiToken::class]);
Route::post('/coBuildAgent/contributeData.json', ['uses' => '\App\Http\Controllers\CoBuildAgentController@contributeData','middleware' => \App\Http\Middleware\ApiToken::class]);







