<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAdmin\BuildingController;
use App\Http\Controllers\ApiAdmin\AuthController;
use App\Http\Controllers\ApiAdmin\DashboardController;
use App\Http\Controllers\ApiAdmin\ApartmentController;
use App\Http\Controllers\ApiAdmin\ResidentController;
use App\Http\Controllers\ApiAdmin\InvoiceController;
use App\Http\Controllers\ApiAdmin\VehicleController;
use App\Http\Controllers\ApiAdmin\StaffController;

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

Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::group(['middleware' => 'check_auth', 'cors'], function () {
        //user
        Route::group(['prefix' => 'me'], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        //dashboard
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/stats/{id}', [DashboardController::class, 'statsBuildingById']);
            Route::get('/stats-all', [DashboardController::class, 'statsAllBuildings']);
        });

        //apartment
        Route::group(['prefix' => 'apartment'], function () {
            Route::get('/list-by-building/{id}', [ApartmentController::class, 'getListByBuilding']);
            Route::post('/create', [ApartmentController::class, 'create']);
            Route::post('/apartment/{id}/add-multiple-residents', [ApartmentController::class, 'addMultipleResidents']);
            Route::get('/apartment/{id}/edit', [ApartmentController::class, 'edit']);
            Route::post('/apartment/{id}/update', [ApartmentController::class, 'update']);
        });

         //resident
         Route::group(['prefix' => 'resident'], function () {
            Route::get('/resident-list/{id}', [ResidentController::class, 'getListResident']);
            Route::post('/create', [ResidentController::class, 'create']);
            Route::get('/resident/{id}/edit', [ResidentController::class, 'edit']);
            Route::post('/{id}/add-apartment', [ResidentController::class, 'addResidentToApartment']);
            Route::post('/{id}/delete-apartment', [ResidentController::class, 'deleteResidentToApartment']);
            Route::post('/update/{id}', [ResidentController::class, 'update']);
        });

         //invoice
         Route::group(['prefix' => 'invoice'], function () {
            Route::get('/list-by-building/{id}', [InvoiceController::class, 'getListInvoice']);
            Route::post('/create', [InvoiceController::class, 'create']);
            Route::get('/edit/{id}', [InvoiceController::class, 'show']);
            Route::post('/update/{id}', [InvoiceController::class, 'update']);
        });

        //vehicle
        Route::group(['prefix' => 'vehicle'], function () {
            Route::get('/list-by-building/{id}', [VehicleController::class, 'getListVehicle']);
            Route::post('/create', [VehicleController::class, 'create']);
            Route::get('/edit/{id}', [VehicleController::class, 'edit']);
            Route::post('/update/{id}', [VehicleController::class, 'update']);
        });
    });

    Route::group(['middleware' => 'auth_admin', 'cors'], function () {
        //building
        Route::group(['prefix' => 'building'], function () {
            Route::get('/building-list', [BuildingController::class, 'getListBuilding']);
            Route::post('/create-building', [BuildingController::class, 'create']);
            Route::get('/edit/{id}', [BuildingController::class, 'edit']);
            Route::put('/update/{id}', [BuildingController::class, 'update']);
        });

        //staff
        Route::group(['prefix' => 'staff'], function () {
            Route::get('/staff-list', [StaffController::class, 'getListStaff']);
            Route::post('/create-staff', [StaffController::class, 'createStaff']);
            Route::delete('/delete-staff/{id}', [StaffController::class, 'deleteStaff']);
        });
    });

    // Route::group(['middleware' => 'auth_staff', 'cors'], function () {
        
    // });
});
