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

Route::get('/', function () {
    return view('welcome', ['website' => 'Lara']);
});

//http://www.lara.inc/blog/public/test
Route::get('/test', 'IndexController@index');

Route::get('posts/{post}/comments/{comment}', function ($postId, $commentId) {
    return $postId . '-' . $commentId;
});

Route::resource('home','HomeController');

Route::get('admin/login','Admin\LoginController@login');