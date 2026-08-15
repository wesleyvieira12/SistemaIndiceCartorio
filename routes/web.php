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

// Recuperação de senha por código
Route::get('password/reset', 'Auth\PasswordResetController@showEmailForm')->name('password.request');
Route::post('password/email', 'Auth\PasswordResetController@sendCode')->name('password.email');
Route::get('password/codigo', 'Auth\PasswordResetController@showCodeForm')->name('password.code.form');
Route::post('password/codigo', 'Auth\PasswordResetController@verifyCode')->name('password.code.verify');
Route::get('password/nova-senha', 'Auth\PasswordResetController@showNewPasswordForm')->name('password.new.form');
Route::post('password/nova-senha', 'Auth\PasswordResetController@updatePassword')->name('password.new.update');
