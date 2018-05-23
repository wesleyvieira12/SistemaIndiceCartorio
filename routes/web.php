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

Route::resource('users', 'UserController')->middleware('auth');
Route::resource('protocolos', 'ProtocoloController')->middleware('auth');
Route::resource('logs', 'LogController')->middleware('auth');

$this->get('/', 'HomeController@index')->name('home');

//DELETAR USUARIO
$this->get('users/{id}/delete','UserController@destroy')->name('users.destroy')->middleware('auth');

//PESQUISAR PROTOCOLOS
$this->any('protocolos/procura','ProtocoloController@procura')->name('protocolos.procura')->middleware('auth');

Auth::routes();
