<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function getListVehicleType()
    {
        $vehicles = VehicleType::all();
        return response()->json($vehicles);
    }
}
