<?php

use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\ResponderController;
use App\Http\Controllers\Api\V1\SurveyController;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');


Route::group(['prefix' => 'v1'], function () {

    Route::apiResource('surveys', SurveyController::class);


    Route::post('/surveys/{survey:public_id}/responders', [ResponderController::class, 'generate']);

    Route::prefix('surveys/{survey}')->controller(QuestionController::class)->group(function () {
        Route::get('/questions', 'index');
        Route::get('/questions/{question}', 'show');
        Route::post('/questions', 'store');
        Route::delete('/questions/{question}', 'destroy');
    });

//    Route::post('/surveys/{survey}/questions', [QuestionController::class, 'store']);

});


