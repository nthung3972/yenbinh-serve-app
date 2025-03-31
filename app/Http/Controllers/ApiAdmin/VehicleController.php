<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\VehicleService;
use App\Helper\Response;
use App\Http\Requests\VehicleRequest\CreateVehicleRequest;
use App\Http\Requests\VehicleRequest\UpdateVehicleRequest;

class VehicleController extends Controller
{
    public function __construct(
        public VehicleService $vehicleService,
    ) {}

    public function getListVehicle(Request $request, $id)
    {
        try {
            $vehicles = $this->vehicleService->getListVehicle($request, $id);
            return Response::data(['data' => $vehicles]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(CreateVehicleRequest $request)
    {
        $filteredVehicles = [];
        foreach ($request->all() as $vehicle) {
            $filteredVehicles[] = collect($vehicle)->only(['building_id', 'apartment_number', 'license_plate', 'vehicle_type', 'parking_slot', 'created_at', 'status'])->toArray();
        }

        try {
            $vehicles = $this->vehicleService->create($filteredVehicles);
            return Response::data(['data' => $vehicles]);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if (str_contains($message, ':')) {
                [$field, $error] = explode(':', $message, 2);
                return Response::dataError($th->getCode(), ['errors' => [$field => [$error]]], $error);
            }
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $vehicle = $this->vehicleService->edit($id);
            return Response::data(['data' => $vehicle]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        try {
            $vehicle = $this->vehicleService
            ->update($request->only('building_id', 'apartment_number', 'license_plate', 'vehicle_type', 'parking_slot', 'created_at', 'status'), $id);
            return Response::data(['data' => $vehicle]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
