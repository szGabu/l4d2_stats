<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if(!env('STATS_API_ONLY'))
{
    Route::get('/', 'App\Http\Controllers\ViewController@playersonline')->name('stats.online');
    Route::get('/playerlist', 'App\Http\Controllers\ViewController@ranking')->name('stats.ranking');
    Route::get('/player', 'App\Http\Controllers\ViewController@player_stats')->name('stats.individual');
    Route::get('/server', 'App\Http\Controllers\ViewController@server_stats')->name('stats.server');
    Route::get('/awards', 'App\Http\Controllers\ViewController@server_awards')->name('stats.awards');
}