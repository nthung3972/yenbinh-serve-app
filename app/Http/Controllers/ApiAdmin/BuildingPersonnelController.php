<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\BuildingPersonnelService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use App\Http\Requests\BuildingPersonnelRequest\CreateBuildingPersonnelRequest;

class BuildingPersonnelController extends Controller
{
    public function __construct(
        public BuildingPersonnelService $buildingPersonnelService,
        public BuildingService $buildingService,
    ) {}

    public function getListBuildingPersonnel(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $personnelList = $this->buildingPersonnelService->getList($id, $request);
            }
            $personnelList = $this->buildingPersonnelService->getList($id, $request);
            return Response::data(['data' => $personnelList]);
        } catch (\Throwable $th) {
            // return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
            return response()->json([
                'error' => [
                    'code' => $th->getCode(),
                    'message' => $th->getMessage()
                ]
            ], 500);
        }
    }

    public function create(CreateBuildingPersonnelRequest $request)
    {
        try {
            $create = $this->buildingPersonnelService->create($request->all());
            return Response::data(['data' => $create]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
