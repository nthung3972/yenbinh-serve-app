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
use App\Http\Controllers\ApiAdmin\ImageUploadController;
use App\Http\Controllers\ApiAdmin\PasswordChangeController;
use App\Http\Controllers\ApiAdmin\DailyReportController;
use App\Http\Controllers\ApiAdmin\ExportController;
use App\Http\Controllers\ApiAdmin\FeeTypeController;
use App\Http\Controllers\ApiAdmin\VehicleTypeController;
use App\Http\Controllers\ApiAdmin\PaymentController;
use App\Http\Controllers\ApiAdmin\DebtController;

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
        Route::get('/verify/{token}', [AuthController::class, 'verify']);
        Route::post('/forgot-password', [PasswordChangeController::class, 'forgotPassword']);
        Route::post('/forgot-password/resend', [PasswordChangeController::class, 'resendForgotPassword'])->middleware('throttle:2,1');
        Route::post('/reset-password', [PasswordChangeController::class, 'resetPassword']);

        Route::group(['prefix' => 'export'], function () {
            Route::get('/invoices/{id}', [ExportController::class, 'exportPrintable']);
        });
    });

    Route::group(['middleware' => 'check_auth', 'cors', 'verified'], function () {
        //email
        Route::group(['prefix' => 'email'], function () {
            Route::post('/resend', [AuthController::class, 'resendVerification'])->middleware(['auth:api', 'throttle_resend']);
        });

        //user
        Route::group(['prefix' => 'me'], function () {
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('profile-update', [AuthController::class, 'updateProfile']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/password-change-request', [PasswordChangeController::class, 'requestChange']);
            Route::post('/password-change-verify', [PasswordChangeController::class, 'verifyChange']);
            Route::post('/resend-password-change', [PasswordChangeController::class, 'resendPasswordChange'])->middleware(['auth:api', 'throttle_resend']);
        });

        //upload
        Route::group(['prefix' => 'upload'], function () {
            Route::post('/upload-image', [ImageUploadController::class, 'upload']);
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
            Route::get('/apartment-numbers/{id}', [ApartmentController::class, 'getApartmentCode']);
            Route::get('/apartment-number/{code}/residents', [ApartmentController::class, 'getResidentsByApartment']);
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

        //fee_type
        Route::group(['prefix' => 'fee-type'], function () {
            Route::get('/flexible', [FeeTypeController::class, 'getFlexibleFee']);
        });

        //invoice
        Route::group(['prefix' => 'invoice'], function () {
            Route::get('/list-by-building/{id}', [InvoiceController::class, 'getListInvoice']);
            Route::get('/apartment-fees/{id}', [InvoiceController::class, 'getApartmentFees']);
            Route::post('/create', [InvoiceController::class, 'create']);
            Route::get('/edit/{id}', [InvoiceController::class, 'show']);
            Route::post('/update/{id}', [InvoiceController::class, 'update']);
        });

        //payment
        Route::group(['prefix' => 'payment'], function () {
            Route::post('/create', [PaymentController::class, 'create']);
        });

        //debt
        Route::group(['prefix' => 'debt'], function () {
            Route::get('/', [DebtController::class, 'index']);
            Route::get('/history', [DebtController::class, 'getDebtHistory']);
            Route::get('/periods', [DebtController::class, 'getPeriods']);
        });
        
        //vehicle
        Route::group(['prefix' => 'vehicle'], function () {
            Route::get('/list-by-building/{id}', [VehicleController::class, 'getListVehicle']);
            Route::post('/create', [VehicleController::class, 'create']);
            Route::get('/edit/{id}', [VehicleController::class, 'edit']);
            Route::post('/update/{id}', [VehicleController::class, 'update']);
        });

        //vehicle_types
        Route::group(['prefix' => 'vehicle-type'], function () {
            Route::get('/list', [VehicleTypeController::class, 'getListVehicleType']);
        });

        //Export
        Route::group(['prefix' => 'export'], function () {
            Route::get('/invoices/{id}', [ExportController::class, 'exportPrintable']);
        });
    });

    Route::group(['middleware' => 'auth_admin', 'cors'], function () {
        //building
        Route::group(['prefix' => 'building'], function () {
            Route::get('/building-list', [BuildingController::class, 'getListBuilding']);
            Route::post('/create', [BuildingController::class, 'create']);
            Route::get('/edit/{id}', [BuildingController::class, 'edit']);
            Route::put('/update/{id}', [BuildingController::class, 'update']);
            Route::delete('/delete/{id}', [BuildingController::class, 'delete']);
        });

        //staff
        Route::group(['prefix' => 'staff'], function () {
            Route::get('/staff-list', [StaffController::class, 'getListStaff']);
            Route::post('/create-staff', [StaffController::class, 'createStaff']);
            Route::delete('/delete-staff/{id}', [StaffController::class, 'deleteStaff']);
        });

        //report
        Route::group(['prefix' => 'admin-report'], function () {
            Route::get('/daily-reports', [DailyReportController::class, 'getAllReports']);
            Route::get('/daily-report/{id}', [DailyReportController::class, 'getDailyReportDetail']);
            Route::delete('/delete-daily-report/{id}', [DailyReportController::class, 'deleteDailyReport']);
        });
    });

    Route::group(['middleware' => 'auth_staff', 'cors'], function () {
        //report
        Route::group(['prefix' => 'report'], function () {
            Route::get('/form-info/{id}', [DailyReportController::class, 'getFormInfo']);
            Route::post('/daily-reports', [DailyReportController::class, 'createDailyReport']);
            Route::get('/daily-reports-by-staff', [DailyReportController::class, 'getReportsByStaff']);
            Route::get('/daily-report/{id}', [DailyReportController::class, 'getDailyReportDetail']);
            Route::put('/update-daily-report/{id}', [DailyReportController::class, 'updateDailyReport']);
        });
    });
});