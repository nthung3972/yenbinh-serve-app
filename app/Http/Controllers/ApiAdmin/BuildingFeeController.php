<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\BuildingFeeService;
use App\Helper\Response;
use App\Http\Requests\BuildingFeeRequest\CreateBuildingFeeRequest;
use App\Http\Requests\BuildingFeeRequest\UpdateBuildingFeeRequest;

class BuildingFeeController extends Controller
{
    public function __construct(
        public BuildingFeeService $buildingFeeService,
    ) {}

    public function feesByBuilding(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingFeeService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $vehicles = $this->buildingFeeService->feesByBuilding($request, $id);
            }
            $vehicles = $this->buildingFeeService->feesByBuilding($request, $id);
            return Response::data(['data' => $vehicles]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $buildingFee = $this->buildingFeeService->show($id);
            return Response::data(['data' => $buildingFee]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function store(CreateBuildingFeeRequest $request)
    {
        try {
            $buildingFee = $this->buildingFeeService->createBuildingFee($request->only(
                'building_id',
                'fee_types_id',
                'fee_subtypes_id',
                'price',
                'unit',
                'effective_from',
                'effective_to',
                'note'
            ));
            return Response::data(['data' => $buildingFee]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateBuildingFeeRequest $request, $id)
    {
        try {
            $update = $this->buildingFeeService->updateBuildingFee($id, $request->only(
                'building_id',
                'fee_types_id',
                'fee_subtypes_id',
                'price',
                'unit',
                'effective_from',
                'effective_to',
                'note'
            ));
            return Response::data(['data' => $update]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function feeManagementByBuilding($id)
    {
        try {
            $feeManagement = $this->buildingFeeService->feeManagementByBuilding($id);
            return Response::data(['data' => $feeManagement]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
