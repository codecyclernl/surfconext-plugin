<?php

Route::group([
    'middleware' => 'web',
], function () {

    Route::get('/surfconext/auth/redirect', 'Codecycler\SURFconext\Http\Controllers\AuthController@redirect');
    Route::get('/surfconext/auth/callback', 'Codecycler\SURFconext\Http\Controllers\AuthController@callback');
    Route::options('/surfconext/auth/callback', 'Codecycler\SURFconext\Http\Controllers\AuthController@callback');
    Route::post('/surfconext/auth/refresh', 'Codecycler\SURFconext\Http\Controllers\AuthController@refresh');
    Route::get('/surfconext/auth/user', 'Codecycler\SURFconext\Http\Controllers\AuthController@user');

    Route::get('/surfconext/user/test', function () {
        return \Auth::getUser();
    });

    Route::get('/surfconext/user/logout', function () {
        \Auth::logout();

        return redirect('/surfconext/auth/redirect');
    });

});
