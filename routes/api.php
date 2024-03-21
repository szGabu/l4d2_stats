<?php

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

Route::get('/player/{steamid}', 'App\Http\Controllers\ApiController@query_player_stats')->name('api.player');
Route::get('/server', 'App\Http\Controllers\ApiController@query_server_stats')->name('api.server');
Route::get('/awards', 'App\Http\Controllers\ApiController@get_awards')->name('api.awards');
Route::get('/stats_query', 'App\Http\Controllers\ApiController@query_stats')->name('api.ranking');
Route::get('/online_query', 'App\Http\Controllers\ApiController@query_online')->name('api.online');
Route::get('/top_10', 'App\Http\Controllers\ApiController@get_top_10')->name('api.top10');

