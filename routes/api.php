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
Auth::routes();

Route::post('v1/register', 'UserController@register');
Route::post('v1/authenticate', 'UserController@authenticate');
Route::post('v1/authenticate/facebook', 'UserController@login_with_fb');

Route::get('v1/open', 'HouseController@open');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('v1/user', 'UserController@getAuthenticatedUser');
    Route::get('v1/closed', 'HouseController@closed');
    Route::get('v1/logout', 'UserController@logout');
});
