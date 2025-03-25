<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAdmin\BuildingController;
use App\Http\Controllers\ApiAdmin\AuthController;
use App\Http\Controllers\ApiAdmin\DashboardController;
use App\Http\Controllers\ApiAdmin\ApartmentController;

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
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::group(['middleware' => 'auth_admin', 'cors'], function () {
        Route::group(['prefix' => 'me'], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/collection-rate', [DashboardController::class, 'getCollectionRateByYear']);
            Route::get('/stats/{id}', [DashboardController::class, 'statsBuildingById']);
            Route::get('/overview', [DashboardController::class, 'overview']);
        });

        //building
        Route::group(['prefix' => 'building'], function () {
            Route::get('/building-list', [BuildingController::class, 'getListBuilding']);
            Route::post('/create-building', [BuildingController::class, 'create']);
            Route::get('/edit/{id}', [BuildingController::class, 'edit']);
            Route::put('/update/{id}', [BuildingController::class, 'update']);
        });

        //apartment
        Route::group(['prefix' => 'apartment'], function () {
            Route::get('/list-by-building/{id}', [ApartmentController::class, 'getListByBuilding']);
            Route::post('/create', [ApartmentController::class, 'create']);
            Route::post('/apartment/{id}/add-multiple-residents', [ApartmentController::class, 'addMultipleResidents']);
            Route::get('/apartment/{id}/edit', [ApartmentController::class, 'edit']);
            Route::post('/apartment/{id}/update', [ApartmentController::class, 'update']);
        });
        
    });
});
