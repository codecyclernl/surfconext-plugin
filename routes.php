<?php

Route::group([
    'middleware' => 'web',
], function () {

    Route::get('/oidc/auth/redirect', 'Codecycler\SURFconext\Http\Controllers\AuthController@redirect');
    Route::get('/oidc/auth/callback', 'Codecycler\SURFconext\Http\Controllers\AuthController@callback');
    Route::options('/oidc/auth/callback', 'Codecycler\SURFconext\Http\Controllers\AuthController@callback');
    Route::post('/oidc/auth/refresh', 'Codecycler\SURFconext\Http\Controllers\AuthController@refresh');

});
