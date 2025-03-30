<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\VehicleService;
use App\Helper\Response;

class VehicleController extends Controller
{
    public function __construct(
        public VehicleService $vehicleService,
    ) {}

    public function getListVehicle(Request $request, $id)
    {
        try {
            $vehicles = $this->vehicleService->getListVehicle($request, $id);
            dd($vehicles->toArray());
            return Response::data(['data' => $vehicles]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
