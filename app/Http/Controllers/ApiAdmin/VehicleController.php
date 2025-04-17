<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\VehicleService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use App\Http\Requests\VehicleRequest\CreateVehicleRequest;
use App\Http\Requests\VehicleRequest\UpdateVehicleRequest;
use App\Exceptions\ValidationException;

class VehicleController extends Controller
{
    public function __construct(
        public VehicleService $vehicleService,
        public BuildingService $buildingService,
    ) {}

    public function getListVehicle(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $vehicles = $this->vehicleService->getListVehicle($request, $id);
            }
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
            $filteredVehicles[] = collect($vehicle)->only(['building_id', 'apartment_number', 'license_plate', 'vehicle_type_id', 'parking_slot', 'created_at', 'status', 'resident_id', 'vehicle_company', 'vehicle_model', 'vehicle_color'])->toArray();
        }

        try {
            $vehicles = $this->vehicleService->create($filteredVehicles);
            return Response::data($vehicles);
        } catch (ValidationException $e) {
            return Response::dataError($e->getCode(), $e->getErrors(), "Lỗi xác thực dữ liệu");
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode() ?: 500, ['general' => [$th->getMessage()]], "Lỗi hệ thống");
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
            ->update($request->only('building_id', 'apartment_number', 'license_plate', 'vehicle_type_id', 'vehicle_company', 'vehicle_model', 'vehicle_color', 'parking_slot', 'created_at', 'status'), $id);
            return Response::data(['data' => $vehicle]);
        } catch (ValidationException $e) {
            return Response::dataError($e->getCode(), $e->getErrors(), "Lỗi xác thực dữ liệu");
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode() ?: 500, ['general' => [$th->getMessage()]], "Lỗi hệ thống");
        }
    }
}
