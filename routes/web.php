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

Route::get('/', 'TopicsController@index')->name('root');

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::resource('users', 'UserController', ['only' => ['show', 'edit', 'update']]);

// 话题路由
Route::resource('topics', 'TopicsController', ['only' => ['index', 'create', 'store', 'update', 'edit', 'destroy']]);
Route::get('topics/{topic}/{slug?}', 'TopicsController@show')->name('topics.show');

// 帖子图片上传
Route::post('upload_image', 'TopicsController@uploadImage')->name('topics.upload_image');

Route::resource('categories', 'CategoriesController', ['only' => ['show']]);

// 话题回复路由
Route::resource('replies', 'RepliesController', ['only' => ['store', 'destroy']]);

// 用户通知路由
Route::resource('notifications', 'NotificationsController', ['only' => ['index']]);

// 后台无访问权限跳转路由
Route::get('permission-denied', 'PagesController@permissionDenied')->name('permission-denied');