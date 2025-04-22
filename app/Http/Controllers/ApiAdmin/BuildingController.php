<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\BuildingService;
use App\Http\Requests\GetListBuildingRequest;
use App\Http\Requests\BuildingRequest\CreateBuildingRequest;
use App\Http\Requests\BuildingRequest\UpdateBuildingRequest;
use App\Helper\Response;

class BuildingController extends Controller
{
    public function __construct(
        public BuildingService $buildingService,
    ) {}

    //getListBuilding
    public function getListBuilding(GetListBuildingRequest $request)
    {
        try {
            $buildings = $this->buildingService->getListBuilding($request);
            return Response::data(['data' => $buildings]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(CreateBuildingRequest $request)
    {
        try {
            $create = $this->buildingService->createBuilding($request->only([
                'name',
                'address',
                'floors',
                'image',
                'total_area',
                'status',
                'building_type'
            ]));
            return Response::data(['data' => $create]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function edit(int $id)
    {
        try {
            $building = $this->buildingService->getBuildingByID($id);
            return Response::data(['data' => $building]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(int $id, UpdateBuildingRequest $request)
    {
        try {
            $update = $this->buildingService->updateBuilding($id, $request->only(
                'name',
                'address',
                'floors',
                'image',
                'total_area',
                'status',
                'building_type'
            ));
            return Response::data(['data' => $update]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $building = $this->buildingService->delete($id);
            return Response::data(['data' => $building]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
