<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', 'LandingController@index')->name('landing');

Route::resource('users', 'UserController')->middleware('auth');
Route::resource('protocolos', 'ProtocoloController')->middleware('auth');
Route::resource('logs', 'LogController')->middleware('auth');
Route::resource('emails-agendados', 'ScheduledEmailController')->middleware('auth');
Route::post('emails-agendados/{id}/cancel', 'ScheduledEmailController@cancel')
    ->name('emails-agendados.cancel')
    ->middleware('auth');

Route::get('/painel', 'HomeController@index')->name('home');

Route::get('users/{id}/delete', 'UserController@destroy')->name('users.destroy')->middleware('auth');

// Autenticação (sem registro público)
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Recuperação de senha
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
