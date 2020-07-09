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

Route::get('/', 'HomeController@index')->name('index');
Route::get('/', function()
{
    return 'Access to this webportal is restricted to authorized users ony<br>For access, please send an email to Adnan_AbbasMalik@mentor.com';
});

Route::get('/{team}/{code}', 'HomeController@teamview')->name('teamview');


