<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
| Created by titugeorge@gmail.com
*/

Route::get('/', function () {

    if($value = session('LoggedUser')) {
        //Redirect if logged in
        return redirect('dashboard');

    } else {
        //Render if not logged in
        return view('home');

    }
});

Route::get('/logout', function () {

    session()->forget('LoggedUser');
    session()->flush();
    return redirect('/');
});

Route::get('/dashboard', function () {

    if(!$value = session('LoggedUser')) {
        //Redirect if not logged in
        return redirect('/');
    }

    return view('dashboard', $value);
});


Route::post('/signup', 'AuthenticationController@insert_user');
Route::post('/login', 'AuthenticationController@validate_login');
Route::any('/social_fb_login', 'AuthenticationController@social_fb_auth');
Route::any('/social_google_login', 'AuthenticationController@social_google_auth');
