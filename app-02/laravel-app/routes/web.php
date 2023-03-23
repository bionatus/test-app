<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Route::get('/', 'WelcomeController@show');

// Route::get('/home', 'HomeController@index')->name('home');

Route::get('/oauth/user', function(Request $request) {
    return Auth::user();
})->middleware('auth:oauth');

Route::get('/oauth/authorize', 'OAuth\AuthorizationController@authorize')->middleware(['web', 'auth']);

Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout');

Route::put('/settings/profile/details', 'ProfileDetailsController@update');

Route::get('/get-accreditated', function() {
    return view('layouts.accreditation');
});

Route::get('/pdf', function() {
    return view('emails.accreditation.pdf', ['name' => $_GET['name'], 'date' => $_GET['date']]);
})->name('pdf');

Route::get('/success', function() {
    if (Auth::check()) {
        Auth::logout();
        session()->flush();
    }

    return Response::view('success');
});

Nova::routes();
