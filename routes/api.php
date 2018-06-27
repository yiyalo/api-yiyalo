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

Route::post('user/register', 'APIRegisterController@register');


Route::group(['middleware' => 'cors', 'prefix' => '/v1'], function(){
    //For user authentication
    Route::post('/login', 'UserController@authenticate');
    Route::post('/register', 'UserController@register');
    Route::get('/logout/{api_token}', 'UserController@logout');

    //For Car 
    Route::get('/cars', 'CarController@index');
    Route::get('/cars/{id}', 'CarController@show');
    Route::post('/cars/save', 'CarController@store');
    Route::post('/cars/update', 'CarController@update');
    Route::get('/cars/delete/{id}/{api_token}', 'CarController@delete');
    
});


