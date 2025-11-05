<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\PolbanApiController;
use App\Http\Middleware\XAuthAPIMiddleware;

//x auth
Route::middleware(XAuthAPIMiddleware::class)->group(function () {

    Route::prefix('countries')->group(function () {
        Route::controller(CountryController::class)->group(function () {
            Route::get('/get', 'get')->name('countries.get');
        });
    });

    Route::prefix('polban')->group(function () {
        Route::controller(PolbanApiController::class)->group(function () {
            Route::post('/briva/response', 'getBrivaResponse')->name('polban.briva.response');
            Route::post('/briva/create_payment', 'createBrivaPayment')->name('polban.briva.create_payment');

            Route::post('/login', 'loginToPolbanAPI')->name('polban.login');
        });
    });
});

// non x auth

