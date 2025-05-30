<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\ResponderController;
use App\Http\Controllers\Api\V1\SurveyController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');


Route::post('/refresh', [Authcontroller::class, 'refresh']);
Route::post('/register', [Authcontroller::class, 'register']);
Route::post('/login', [Authcontroller::class, 'login']);
Route::post('/logout', [Authcontroller::class, 'logout'])->middleware('auth:api');
Route::post('/whoami', [Authcontroller::class, 'whoami'])->middleware('auth:api');



Route::group(['prefix' => 'v1'], function () {

    Route::controller(SurveyController::class)
        ->middleware('auth:api')
        ->group(function () {
            Route::get('/surveys', 'index');
            Route::post('/surveys', 'store');
            Route::get('/surveys/{survey}', 'show')->can('view', 'survey');
            Route::patch('/surveys/{survey}', 'update')->can('update', 'survey');
            Route::delete('/surveys/{survey}', 'destroy')->can('delete', 'survey');
        })
    ;

    Route::prefix('surveys/{survey}')
        ->controller(QuestionController::class)
        ->middleware(['auth:api', 'can:access,survey'])
        ->group(function () {
        Route::get('/questions', 'index');
        Route::post('/questions', 'store');
        Route::get('/questions/{question}', 'show');
        Route::patch('/questions/{question}', 'update');
        Route::delete('/questions/{question}', 'destroy');
    });


    //    Route::post('/surveys/{survey:public_id}/responders', [ResponderController::class, 'generate']);
    //    Route::post('/surveys/{survey}/questions', [QuestionController::class, 'store']);

});


