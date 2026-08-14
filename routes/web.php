<?php

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

Route::get('/', 'LandingController@index')->name('landing');

Route::resource('users', 'UserController')->middleware('auth');
Route::resource('protocolos', 'ProtocoloController')->middleware('auth');
Route::resource('logs', 'LogController')->middleware('auth');

Route::get('/painel', 'HomeController@index')->name('home');

//DELETAR USUARIO
Route::get('users/{id}/delete', 'UserController@destroy')->name('users.destroy')->middleware('auth');

Auth::routes();
