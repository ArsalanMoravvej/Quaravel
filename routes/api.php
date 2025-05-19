<?php

use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');

Route::apiResource('surveys', SurveyController::class);


//Route::get('/surveys/{survey:public_id}', [SurveyController::class, 'publicShow']);
