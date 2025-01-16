<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\BuildingService;
use App\Http\Requests\GetListBuildingRequest;
use App\Http\Requests\CreateBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;

class BuildingController extends Controller
{
    public function __construct(
        public BuildingService $buildingService,
    ) {
    }

    public function getListBuilding(GetListBuildingRequest $request)
    {
        $buildings = $this->buildingService->getListBuilding($request->all());
        return response()->json(['message' => $buildings]);
    }

    public function create(CreateBuildingRequest $request)
    {
        $create = $this->buildingService->createBuilding($request->all());
        return response()->json(['message' => $create]);
    }

    public function edit(int $id)
    {
        $building = $this->buildingService->getBuildingByID($id);
        return response()->json(['message' => $building]);
    }

    public function update(int $id, UpdateBuildingRequest $request)
    {
        $update = $this->buildingService->updateBuilding($id, $request->all());
        return response()->json(['message' => $update]); 
    }

    public function show($id)
    {
        // Return a specific resource
    }
}
