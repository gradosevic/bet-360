<?php

use Illuminate\Http\Request;

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

Route::group(['middleware'=>'auth:api'], function()
{
    Route::get('users', 'ApiController@users');
    Route::get('user/{id}', 'ApiController@getUser');
    Route::put('user', 'ApiController@createUser');
    Route::post('user', 'ApiController@updateUser');

    Route::post('deposit', 'ApiController@deposit');
    Route::post('withdrawal', 'ApiController@withdrawal');

    Route::get('transaction/{id}', 'ApiController@transaction');

    Route::get('transactions', 'ApiController@transactions');
    Route::post('transactions', 'ApiController@transactions');

    Route::post('reports', 'ApiController@reports');
});
