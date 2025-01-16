<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAdmin\BuildingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'admin'], function () {
    // Route::group(['prefix' => 'auth'], function () {
    //     Route::post('login', 'ApiAdmin\AuthController@login');
    //     Route::post('forgot-password', 'ApiAdmin\AuthController@sendEmailPasswordReset');
    //     Route::post('reset-password', 'ApiAdmin\AuthController@resetPassword');
    //     Route::get('verify', 'ApiAdmin\AuthController@verify');
    //     Route::post('check-token','ApiAdmin\AuthController@checkToken');
    // });

    //building
    Route::group(['prefix' => 'building'], function () {
        Route::get('/building-list', [BuildingController::class, 'getListBuilding']);
        Route::post('/create-building', [BuildingController::class, 'create']);
        Route::get('/edit/{id}', [BuildingController::class, 'edit']);
        Route::put('/update/{id}', [BuildingController::class, 'update']);
    });
});
