<?php

use App\Http\Controllers\ResponderController;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');

Route::apiResource('surveys', SurveyController::class);


Route::post('/surveys/{survey:public_id}/responders', [ResponderController::class, 'generate']);
